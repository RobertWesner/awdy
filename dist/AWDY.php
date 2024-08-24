<?php /** @noinspection ALL */ 


namespace RobertWesner\AWDY{

use RobertWesner\AWDY\Template\TemplateInterface;

final class AWDY
{
    private static ?int $fixedWidth = null;
    private static ?int $fixedHeight = null;
    private static int $previousWidth = 0;
    private static int $previousHeight = 0;

    private static TemplateInterface $template;

    private static function getWidth(): int
    {
        return self::$fixedWidth ?? (int)exec('tput cols');
    }

    private static function getHeight(): int
    {
        return self::$fixedHeight ?? (int)exec('tput lines');
    }

    private static function render(): void
    {
        $width = self::getWidth();
        $height = self::getHeight();

        if ($width !== self::$previousWidth || $height !== self::$previousHeight) {
            echo self::$template->getBorder()->getBuffer(self::getWidth(), self::getHeight());
            echo AnsiEscape::moveToBeginning();

            self::$previousWidth = $width;
            self::$previousHeight = $height;
        }

        foreach (self::$template->getAreas() as $area) {
            $area->render($width, $height);
            echo AnsiEscape::resetColor();
            echo AnsiEscape::moveToBeginning();
        }
    }

    public static function setUp(TemplateInterface $template, ?int $width = null, ?int $height = null): void
    {
        self::$template = $template;
        self::$fixedWidth = $width;
        self::$fixedHeight = $height;

        echo AnsiEscape::clear();
        echo AnsiEscape::moveToBeginning();

        self::render();
    }

    /**
     * Print to the Template.
     */
    public static function echo(string $echo): void
    {
        self::$template->handleEcho($echo);
        self::render();
    }

    public static function printf(string $string, mixed ...$args): void
    {
        self::echo(sprintf($string, ...$args));
    }

    /**
     * @param float $progress Progress from 0 to 1
     */
    public static function progress(float $progress, int $current = 0, int $total = 0): void
    {
        self::$template->handleProgress($progress, $current, $total);
        self::render();
    }
}



}namespace RobertWesner\AWDY{

/**
 * https://gist.github.com/fnky/458719343aabd01cfb17a3a4f7296797
 *
 * @codeCoverageIgnore
 */
final class AnsiEscape
{
    private const SEQUENCE = "\33[";

    public static function clear(): string
    {
        return self::SEQUENCE . '2J';
    }

    public static function moveToBeginning(): string
    {
        return self::SEQUENCE . 'H';
    }

    public static function moveTo(int $x, int $y): string
    {
        return self::SEQUENCE . $y . ';' . $x . 'f';
    }

    public static function resetColor(): string
    {
        return self::SEQUENCE . '0m';
    }

    public static function fg(int $id): string
    {
        return self::SEQUENCE . '38;5;' . $id . 'm';
    }

    public static function bg(int $id): string
    {
        return self::SEQUENCE . '48;5;' . $id . 'm';
    }
}



}namespace RobertWesner\AWDY\Template{

trait AbsoluteCoordinateTrait
{
    protected function absoluteCoordinate(int $value, int $max): int
    {
        if ($value >= 0) {
            return $value;
        }

        return $max + $value;
    }
}



}namespace RobertWesner\AWDY\Template{

use RobertWesner\AWDY\AnsiEscape;

class Area
{
    use AbsoluteCoordinateTrait;

    /**
     * @param callable|callable-string|array $onRender
     */
    public static function create(int $x1, int $y1, int $x2, int $y2, callable|string|array $onRender): static
    {
        return new static($x1, $y1, $x2, $y2, $onRender);
    }

    private bool $dirty = true;

    private function __construct(
        private readonly int $x1,
        private readonly int $y1,
        private readonly int $x2,
        private readonly int $y2,
        private $onRender,
    ) {
    }

    public function render(int $screenWidth, int $screenHeight): void
    {
        if (!$this->dirty) {
            return;
        }

        $this->dirty = false;

        $width = $this->absoluteCoordinate($this->x2, $screenWidth)
            - $this->absoluteCoordinate($this->x1, $screenWidth) + 1;
        $height = $this->absoluteCoordinate($this->y2, $screenHeight)
            - $this->absoluteCoordinate($this->y1, $screenHeight) + 1;

        // TODO: huh?
        if ($height === 0) {
            $height = 1;
        }

        $buffer = new Buffer($width, $height);
        ($this->onRender)($buffer);

        $y = $this->y1 + 1;
        if ($y < 0) {
            $y = $screenHeight + $y;
        }
        $x = $this->x1 + 1;

        foreach (explode(PHP_EOL, (string)$buffer) as $line) {
            echo AnsiEscape::moveTo($x, $y), $line;

            $y++;
        }
    }

    /**
     * Call if Area needs to be re-rendered.
     */
    public function dirty(): void
    {
        $this->dirty = true;
    }
}



}namespace RobertWesner\AWDY\Template{

// TODO: allow ansi escapes for border parts

class Border
{
    use AbsoluteCoordinateTrait;

    private string $horizontal = ' ';
    private string $vertical = ' ';

    private string $cornerTopLeft = ' ';
    private string $cornerTopRight = ' ';
    private string $cornerBottomLeft = ' ';
    private string $cornerBottomRight = ' ';

    private string $connectFacingLeft = ' ';
    private string $connectFacingRight = ' ';
    private string $connectFacingTop = ' ';
    private string $connectFacingBottom = ' ';
    private string $connectFacingAll = ' ';

    /**
     * @var Connection[]
     */
    private array $connections = [];

    public static function create(): static
    {
        return new static();
    }

    private function __construct()
    {
    }

    private function mapFacing(string $facing): string
    {
        return match ($facing) {
            Facing::RIGHT => $this->connectFacingRight,
            Facing::LEFT => $this->connectFacingLeft,
            Facing::TOP => $this->connectFacingTop,
            Facing::BOTTOM => $this->connectFacingBottom,
            default => $this->connectFacingAll,
        };
    }

    private function drawConnectionNode(
        int $x,
        int $y,
        string $connect,
        Buffer $buffer,
        int $bufferWidth,
        int $bufferHeight,
    ): void {
        $x = $this->absoluteCoordinate($x, $bufferWidth);
        $y = $this->absoluteCoordinate($y, $bufferHeight);

        foreach (explode(PHP_EOL, $connect) as $i => $line) {
            $buffer->draw($x, $y + $i, $line);
        }
    }

    private function drawHorizontal(int $x, int $y, int $width, Buffer $buffer): void
    {
        $horizontalLines = explode(PHP_EOL, $this->horizontal);
        $horizontalLinesWidth = strlen($horizontalLines[0]);
        for ($i = 0; $i < $width; $i += $horizontalLinesWidth) {
            $trimTo = null;
            if ($i + $horizontalLinesWidth > $width) {
                $trimTo = $width - $i - $horizontalLinesWidth;
            }

            foreach ($horizontalLines as $lineY => $line) {
                if ($trimTo !== null) {
                    $line = substr($line, 0, $trimTo);
                }

                $buffer->draw($i + $x, $y + $lineY, $line);
            }
        }
    }

    private function drawVertical(int $x, int $y, int $height, Buffer $buffer): void
    {
        $verticalLines = explode(PHP_EOL, $this->vertical);
        $verticalLinesCount = count($verticalLines);
        for ($i = 0; $i < $height; $i++) {
            $line = $verticalLines[$i % $verticalLinesCount];

            $buffer->draw($x, $i + $y, $line);
        }
    }

    private function getFirstLineWidth(string $string): int
    {
        return strlen(explode(PHP_EOL, $string, 2)[0]);
    }

    public function horizontal(string $horizontal): static
    {
        $this->horizontal = $horizontal;

        return $this;
    }

    public function vertical(string $vertical): static
    {
        $this->vertical = $vertical;

        return $this;
    }

    public function corners(string $topLeft, string $topRight, string $bottomLeft, string $bottomRight): static
    {
        $this->cornerTopLeft = $topLeft;
        $this->cornerTopRight = $topRight;
        $this->cornerBottomLeft = $bottomLeft;
        $this->cornerBottomRight = $bottomRight;

        return $this;
    }

    public function connectFacing(string $left, string $right, string $top, string $bottom, string $all): static
    {
        $this->connectFacingLeft = $left;
        $this->connectFacingRight = $right;
        $this->connectFacingTop = $top;
        $this->connectFacingBottom = $bottom;
        $this->connectFacingAll = $all;

        return $this;
    }

    /**
     * @param Connection[] $connections
     *
     * @codeCoverageIgnore
     */
    public function connections(array $connections): static
    {
        $this->connections = $connections;

        return $this;
    }

    public function getBuffer(int $width, int $height): Buffer
    {
        $buffer = new Buffer($width, $height);

        $lines = explode(PHP_EOL, $this->cornerTopLeft);
        $cornerHeightTop = count($lines);
        $cornerWidth = strlen($lines[0]);
        foreach ($lines as $i => $line) {
            $buffer->draw(0, $i, $line);
        }

        foreach (explode(PHP_EOL, $this->cornerTopRight) as $i => $line) {
            $buffer->draw($width - strlen($line), $i, $line);
        }

        $lines = explode(PHP_EOL, $this->cornerBottomLeft);
        $linesCount = count($lines);
        foreach ($lines as $i => $line) {
            $buffer->draw(0, $height - $linesCount + $i, $line);
        }

        $lines = explode(PHP_EOL, $this->cornerBottomRight);
        $cornerHeightBottom = $linesCount = count($lines);
        foreach ($lines as $i => $line) {
            $buffer->draw($width - strlen($line), $height - $linesCount + $i, $line);
        }

        // Left bar
        $this->drawVertical(
            0,
            $cornerHeightTop,
            $height - $cornerHeightTop - $cornerHeightBottom,
            $buffer,
        );

        // Right bar
        $this->drawVertical(
            $width - $this->getFirstLineWidth($this->vertical),
            $cornerHeightTop,
            $height - $cornerHeightTop - $cornerHeightBottom,
            $buffer,
        );

        // Top bar
        $this->drawHorizontal($cornerWidth, 0, $width - $cornerWidth * 2, $buffer);

        // Bottom bar
        $this->drawHorizontal(
            $cornerWidth,
            $height - substr_count($this->horizontal, PHP_EOL) - 1,
            $width - $cornerWidth * 2,
            $buffer,
        );

        foreach ($this->connections as $connection) {
            $beginX = $this->absoluteCoordinate($connection->beginX, $width);
            $beginY = $this->absoluteCoordinate($connection->beginY, $height);
            $beginConnection = $this->mapFacing($connection->beginFacing);
            $endX = $this->absoluteCoordinate($connection->endX, $width);
            $endY = $this->absoluteCoordinate($connection->endY, $height);
            $endConnection = $this->mapFacing($connection->endFacing);

            $this->drawConnectionNode(
                $beginX,
                $beginY,
                $beginConnection,
                $buffer,
                $width,
                $height,
            );

            $this->drawConnectionNode(
                $endX,
                $endY,
                $endConnection,
                $buffer,
                $width,
                $height,
            );

            if ($connection->type === Connection::TYPE_HORIZONTAL) {
                $firstLineWidth = $this->getFirstLineWidth($beginConnection);
                $this->drawHorizontal(
                    $beginX + $firstLineWidth,
                    $beginY,
                    $endX - $beginX - $firstLineWidth,
                    $buffer,
                );
            } elseif ($connection->type === Connection::TYPE_VERTICAL) {
                $this->drawVertical(
                    $beginX,
                    $beginY + substr_count($beginConnection, PHP_EOL) + 1,
                    $endY - $beginY - substr_count($beginConnection, PHP_EOL) - 1,
                    $buffer,
                );
            }
        }

        return $buffer;
    }
}



}namespace RobertWesner\AWDY\Template{

use RobertWesner\AWDY\AnsiEscape;

final class Buffer
{
    use AbsoluteCoordinateTrait;

    private string $buffer;
    private array $ansiEscapes = [];

    public function __construct(
        private readonly int $width,
        private readonly int $height,
    ) {
        $this->buffer = substr(str_repeat(str_repeat(' ', $width) . PHP_EOL, $height), 0, -1);
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    private function drawMultiline(
        int $x,
        int $y,
        string $text,
        ?string $ansiEscape = null,
        ?string $transparency = null,
    ): void {
        foreach (explode(PHP_EOL, $text) as $i => $line) {
            $this->draw($x, $y + $i, $line, $ansiEscape, $transparency);
        }
    }

    /**
     * Draw a string of characters to the Buffer.
     */
    public function draw(int $x, int $y, string $text, ?string $ansiEscape = null, ?string $transparency = null): void
    {
        // TODO: clip overflow! right now it breaks everything

        $x = $this->absoluteCoordinate($x, $this->width);
        $y = $this->absoluteCoordinate($y, $this->height);

        if (strpos($text, PHP_EOL)) {
            $this->drawMultiline($x, $y, $text, $ansiEscape, $transparency);

            return;
        }

        if (!isset($this->ansiEscapes[$y])) {
            $this->ansiEscapes[$y] = [];
        }

        if ($transparency === null) {
            // Simple and efficient. Just replacing Text.
            if ($ansiEscape !== null) {
                $this->ansiEscapes[$y][$x] = AnsiEscape::resetColor() . $ansiEscape;
                $this->ansiEscapes[$y][$x + strlen($text)] = AnsiEscape::resetColor();
            }

            $this->buffer = substr_replace(
                $this->buffer,
                $text,
                $y * ($this->width + 1) + $x,
                strlen($text),
            );
        } else {
            // Character by character, excluding the transparency
            $i = 0;
            foreach (str_split($text) as $character) {
                if ($character === $transparency) {
                    $i++;

                    continue;
                }

                if ($ansiEscape !== null) {
                    $this->ansiEscapes[$y][$x + $i] = AnsiEscape::resetColor() . $ansiEscape;
                }

                $this->buffer = substr_replace(
                    $this->buffer,
                    $character,
                    $y * ($this->width + 1) + $x + $i,
                    1,
                );

                $i++;
            }

            $xPos = $x + $i + 1;
            $this->ansiEscapes[$y][$xPos] = AnsiEscape::resetColor() . ($this->ansiEscapes[$y][$xPos] ?? '');
        }
    }

    public function debug(): void
    {
        $this->buffer = str_replace(' ', '#', $this->buffer);
    }

    public function __toString()
    {
        $buffer = $this->buffer;
        $escapes = $this->ansiEscapes;

        krsort($escapes);

        foreach ($escapes as $y => $escapeLine) {
            krsort($escapeLine);

            foreach ($escapeLine as $x => $escape) {
                $buffer = substr_replace($buffer, $escape, $y * ($this->width + 1) + $x, 0);
            }
        }

        return $buffer;
    }
}



}namespace RobertWesner\AWDY\Template{

class BufferLogger
{
    private string $log = '';

    public function append(string $message): void
    {
        $this->log .= $message;
    }

    public function renderTo(Buffer $buffer): void
    {
        $lines = [];
        foreach (explode(PHP_EOL, rtrim($this->log, PHP_EOL)) as $line) {
            if (strlen($line) > $buffer->getWidth()) {
                foreach (str_split($line, $buffer->getWidth()) as $splitLine) {
                    $lines[] = $splitLine;
                }
            } else {
                $lines[] = $line;
            }
        }

        foreach (array_slice($lines, - $buffer->getHeight()) as $i => $line) {
            $buffer->draw(0, $i, $line);
        }
    }
}



}namespace RobertWesner\AWDY\Template{

class Connection
{
    public const TYPE_HORIZONTAL = 'horizontal';
    public const TYPE_VERTICAL = 'vertical';

    public int $beginX;
    public int $beginY;
    public string $beginFacing;
    public int $endX;
    public int $endY;
    public string $endFacing;

    private function __construct(
        public string $type,
    ) {
    }

    public static function horizontal(): static
    {
        return new static(static::TYPE_HORIZONTAL);
    }

    public static function vertical(): static
    {
        return new static(static::TYPE_VERTICAL);
    }

    public function begin(int $x, int $y, string $facing): static
    {
        $this->beginX = $x;
        $this->beginY = $y;
        $this->beginFacing = $facing;

        return $this;
    }

    public function end(int $x, int $y, string $facing): static
    {
        $this->endX = $x;
        $this->endY = $y;
        $this->endFacing = $facing;

        return $this;
    }
}



}namespace RobertWesner\AWDY\Template{

/**
 * TODO: refactor to enum?
 */
class Facing
{
    public const LEFT = 'left';
    public const RIGHT = 'right';
    public const TOP = 'top';
    public const BOTTOM = 'bottom';
    public const ALL = 'all';
}



}namespace RobertWesner\AWDY\Template{

interface TemplateInterface
{
    public function getBorder(): Border;

    /**
     * @return Area[]
     */
    public function getAreas(): array;

    public function handleEcho(string $echo): void;

    /**
     * @param float $progress 0 - 1
     */
    public function handleProgress(float $progress, int $current = 0, int $total = 0): void;
}



}namespace RobertWesner\AWDY\Template\Templates{

use RobertWesner\AWDY\Template\Area;
use RobertWesner\AWDY\Template\Buffer;
use RobertWesner\AWDY\Template\TemplateInterface;

abstract class AbstractCanvasTemplate implements TemplateInterface
{
    protected Area $canvas;

    protected float $progress = 0;

    /**
     * return [$x1, $y1, $x2, $y2];
     *
     * @return array<int, int, int, int>
     */
    abstract protected function getCanvasDimensions(): array;

    abstract public function renderCanvas(Buffer $buffer): void;

    public function __construct()
    {
        [$x1, $y1, $x2, $y2] = $this->getCanvasDimensions();

        $this->canvas = Area::create($x1, $y1, $x2, $y2, [$this, 'renderCanvas']);
    }

    public function getAreas(): array
    {
        return [
            $this->canvas,
        ];
    }

    public function handleProgress(float $progress, int $current = 0, int $total = 0): void
    {
        $this->progress = $progress;
        $this->canvas->dirty();
    }
}



}namespace RobertWesner\AWDY\Template\Templates{

use RobertWesner\AWDY\AnsiEscape;
use RobertWesner\AWDY\Template\Area;
use RobertWesner\AWDY\Template\Border;
use RobertWesner\AWDY\Template\Buffer;
use RobertWesner\AWDY\Template\BufferLogger;
use RobertWesner\AWDY\Template\Connection;
use RobertWesner\AWDY\Template\Facing;
use RobertWesner\AWDY\Template\TemplateInterface;

// TODO: rework this to something actually nice

class DefaultTemplate implements TemplateInterface
{
    private Area $logArea;
    private Area $progressArea;

    private float $progress = 0;
    private BufferLogger $logger;

    public function __construct()
    {
        $this->logger = new BufferLogger();

        $this->logArea = Area::create(5, 3, -6, -10, function (Buffer $buffer) {
            $this->logger->renderTo($buffer);
        });

        $this->progressArea = Area::create(5, -6, -6, -4, function (Buffer $buffer) {
            $buffer->draw(1, 0, '.', AnsiEscape::fg(8));
            $buffer->draw(2, 0, str_repeat('-', $buffer->getWidth() - 4), AnsiEscape::fg(8));
            $buffer->draw(-2, 0, '.', AnsiEscape::fg(8));
            $buffer->draw(1, 2, '\'', AnsiEscape::fg(8));
            $buffer->draw(2, 2, str_repeat('-', $buffer->getWidth() - 4), AnsiEscape::fg(8));
            $buffer->draw(-2, 2, '\'', AnsiEscape::fg(8));

            $buffer->draw(1, 1, '|', AnsiEscape::fg(8));
            $progressBarWidth = $buffer->getWidth() - 4;
            $progress = $progressBarWidth * $this->progress;
            $buffer->draw(
                2,
                1,
                str_repeat('#', (int)$progress),
                AnsiEscape::bg(2) . AnsiEscape::fg(2),
            );
            $buffer->draw(-2, 1, '|', AnsiEscape::fg(8));
        });
    }

    public function getBorder(): Border
    {
        return Border::create()
            ->horizontal(<<<'EOF'
            -
             
            -
            EOF)
            ->vertical(<<<'EOF'
            |   |
            EOF)
            ->corners(
                topLeft: <<<'EOF'
                .----
                |    
                |   .
                EOF,
                topRight: <<<'EOF'
                ----. 
                    |
                .   |
                EOF,
                bottomLeft: <<<'EOF'
                |   '
                |    
                '----
                EOF,
                bottomRight: <<<'EOF'
                '   |
                    |
                ----'
                EOF,
            )
            ->connectFacing(
                left: <<<'EOF'
                '   |
                    |
                .   |
                EOF,
                right: <<<'EOF'
                |   '
                |    
                |   .
                EOF,
                top: <<<'EOF'
                .   .
                     
                -----
                EOF,
                bottom: <<<'EOF'
                -----
                     
                .   .
                EOF,
                all: <<<'EOF'
                '   '
                     
                .   .
                EOF,
            )
            ->connections([
                Connection::horizontal()
                    ->begin(0, -9, Facing::RIGHT)
                    ->end(-5, -9, Facing::LEFT),
            ]);
    }

    public function getAreas(): array
    {
        return [
            $this->logArea,
            $this->progressArea,
        ];
    }

    public function handleEcho(string $echo): void
    {
        $this->logger->append($echo);
        $this->logArea->dirty();
    }

    public function handleProgress(float $progress, int $current = 0, int $total = 0): void
    {
        $this->progress = $progress;
        $this->progressArea->dirty();
    }
}



}namespace RobertWesner\AWDY\Template\Templates{

use RobertWesner\AWDY\Template\Area;
use RobertWesner\AWDY\Template\Border;
use RobertWesner\AWDY\Template\Buffer;
use RobertWesner\AWDY\Template\TemplateInterface;

/**
 * No logging. Instead, has time elapsed and memory usage.
 */
class JustProgressTemplate implements TemplateInterface
{
    private Area $progressArea;

    private int $beginTime;

    private float $progress = 0;
    private int $current = 0;
    private int $total = 0;

    public function __construct(int $progressBarWidth = 32)
    {
        $this->beginTime = time();

        $memoryLimit = 0;
        $limit = ini_get('memory_limit');
        if ($limit >= 0) {
            $units = ['K' => 1, 'M' => 2, 'G' => 3];
            $unit = strtoupper(substr($limit, -1));
            $memoryLimit = (int)substr($limit, 0, -1) * pow(1024, $units[$unit] ?? 0);
        }

        $this->progressArea = Area::create(0, 0, -1, 0, function (
            Buffer $buffer,
        ) use (
            $progressBarWidth,
            $memoryLimit,
        ) {
            $timePassed = date('H:i:s', time() - $this->beginTime);
            $buffer->draw(0, 0, $timePassed);

            $counter = '';
            if ($this->total !== 0) {
                $counter = str_pad((string)$this->current, strlen((string)$this->total), ' ', STR_PAD_LEFT)
                    . '/' . $this->total;
                $buffer->draw(strlen($timePassed) + 1, 0, $counter);
            }

            $progressX = strlen($timePassed) + 1;
            if ($counter !== '') {
                $progressX += strlen($counter) + 1;
            }

            $buffer->draw($progressX, 0, '[');
            $progress = $progressBarWidth * $this->progress;
            $buffer->draw($progressX + 1, 0, str_repeat('=', (int)$progress) . ($this->progress < 1 ? '>' : ''));
            $buffer->draw($progressX + $progressBarWidth + 2, 0, ']');

            $memoryInformation = '';
            if ($memoryLimit > 0) {
                $memoryUsage = memory_get_usage();
                $memoryInformation = sprintf(
                    'Memory[%05.2f%%]: %s/%d',
                    $memoryUsage / $memoryLimit,
                    str_pad((string)$memoryUsage, strlen((string)$memoryLimit), ' ', STR_PAD_LEFT),
                    $memoryLimit,
                );
            }
            $buffer->draw($progressX + $progressBarWidth + 4, 0, $memoryInformation);
        });
    }

    public function getBorder(): Border
    {
        return Border::create();
    }

    public function getAreas(): array
    {
        return [
            $this->progressArea,
        ];
    }

    public function handleEcho(string $echo): void
    {
    }

    public function handleProgress(float $progress, int $current = 0, int $total = 0): void
    {
        $this->progress = $progress;
        $this->current = $current;
        $this->total = $total;
        $this->progressArea->dirty();
    }
}



}namespace RobertWesner\AWDY\Template\Templates{

use RobertWesner\AWDY\Template\Area;
use RobertWesner\AWDY\Template\Border;
use RobertWesner\AWDY\Template\Buffer;
use RobertWesner\AWDY\Template\BufferLogger;
use RobertWesner\AWDY\Template\Connection;
use RobertWesner\AWDY\Template\Facing;
use RobertWesner\AWDY\Template\TemplateInterface;

class SimpleTemplate implements TemplateInterface
{
    private Area $logArea;
    private Area $progressArea;

    private float $progress = 0;
    private int $current = 0;
    private int $total = 0;
    private BufferLogger $logger;

    public function __construct()
    {
        $this->logger = new BufferLogger();

        $this->logArea = Area::create(2, 3, -3, -2, function (Buffer $buffer) {
            $this->logger->renderTo($buffer);
        });

        $this->progressArea = Area::create(2, 1, -3, 1, function (Buffer $buffer) {
            $counter = '';
            if ($this->total !== 0) {
                $counter = str_pad((string)$this->current, strlen((string)$this->total), ' ', STR_PAD_LEFT)
                    . '/' . $this->total;
                $buffer->draw(0, 0, $counter);
            }

            $progressX = 0;
            if ($counter !== '') {
                $progressX = strlen($counter) + 1;
            }

            $buffer->draw($progressX, 0, '[');
            $progressBarWidth = $buffer->getWidth() - strlen($counter) - 2;
            $progress = $progressBarWidth * $this->progress;
            $buffer->draw($progressX + 1, 0, str_repeat('=', (int)$progress) . ($this->progress < 1 ? '>' : ''));
            $buffer->draw(-1, 0, ']');
        });
    }

    public function getBorder(): Border
    {
        return Border::create()
            ->horizontal('-')
            ->vertical('|')
            ->corners('.', '.', '\'', '\'')
            ->connectFacing('+', '+', '+', '+', '+')
            ->connections([
                Connection::horizontal()
                    ->begin(0, 2, Facing::RIGHT)
                    ->end(-1, 2, Facing::LEFT),
            ]);
    }

    public function getAreas(): array
    {
        return [
            $this->logArea,
            $this->progressArea,
        ];
    }

    public function handleEcho(string $echo): void
    {
        $this->logger->append($echo);
        $this->logArea->dirty();
    }

    public function handleProgress(float $progress, int $current = 0, int $total = 0): void
    {
        $this->progress = $progress;
        $this->current = $current;
        $this->total = $total;
        $this->progressArea->dirty();
    }
}



}namespace RobertWesner\AWDY\Template\Templates{

use RobertWesner\AWDY\AnsiEscape;
use RobertWesner\AWDY\Template\Border;
use RobertWesner\AWDY\Template\Buffer;

class SnugglyTemplate extends AbstractCanvasTemplate
{
    public const SNUGGLY_SPRITES = [<<<'EOF'
    #_##############
    | |#######/\/\##
    \ \______/ . .\#
    #|          ^ |#
    #|           /##
    #/ /______/ /\##
    /_/\_\###/_/\_\#
    EOF, <<<'EOF'
    #_##############
    | |#######/\/\##
    \ \______/ . .\#
    #|          ^ |#
    #|           /##
    #/ /______/ /\##
    /_/\_\###/_/\_\#
    EOF, <<<'EOF'
    #_##############
    | |#######/\/\##
    \ \______/ . .\#
    #|          ^ |#
    #|           /##
    #| |_____| | |##
    #|_|_|###|_|_|##
    EOF, <<<'EOF'
    #_##############
    | |#######/\/\##
    \ \______/ . .\#
    #|          ^ |#
    #|           /##
    #/\ \______\ \##
    /_/\_\###/_/\_\#
    EOF, <<<'EOF'
    #_##############
    | |#######/\/\##
    \ \______/ . .\#
    #|          ^ |#
    #|           /##
    #/\ \______\ \##
    /_/\_\###/_/\_\#
    EOF, <<<'EOF'
    #_##############
    | |#######/\/\##
    \ \______/ . .\#
    #|          ^ |#
    #|           /##
    #| |_____| | |##
    #|_|_|###|_|_|##
    EOF];

    public const STREET_SPRITE = <<<'EOF'
    -
     
     
    _
    EOF;

    private int $snugglyCurrentSprite = 0;

    public function __construct(
        private readonly string $snugglyColor = '',
    ) {
        parent::__construct();
    }

    protected function getCanvasDimensions(): array
    {
        return [6, 7, -7, -5];
    }

    public function getBorder(): Border
    {
        return Border::create()
            ->horizontal(<<<'EOF'
             
             
             
            -
             
             
            -
            EOF)
            ->vertical(<<<'EOF'
            |    |
            EOF)
            ->corners(
                topLeft: <<<'EOF'
                         ___       
                        /   \      
                       /     \     
                .-----'       '----
                |                  
                |                  
                |    .-------------
                EOF,
                topRight: <<<'EOF'
                        ___        
                       /   \       
                      /     \      
                -----'       '----.
                                  |
                                  |
                -------------.    |
                EOF,
                bottomLeft: <<<'EOF'
                |    '-------------
                |                  
                |                  
                '------------------
                EOF,
                bottomRight: <<<'EOF'
                --------------'    |
                                   |
                                   |
                -------------------'
                EOF,
            );
    }

    public function handleEcho(string $echo): void
    {
    }

    public function renderCanvas(Buffer $buffer): void
    {
        $this->snugglyCurrentSprite = ($this->snugglyCurrentSprite + 1) % count(self::SNUGGLY_SPRITES);
        $snugglySprite = self::SNUGGLY_SPRITES[$this->snugglyCurrentSprite];

        $snugglyX = $this->progress * ($buffer->getWidth() - strpos($snugglySprite, PHP_EOL));

        for ($i = 0; $i < $buffer->getWidth(); $i++) {
            $buffer->draw($i, -6, self::STREET_SPRITE, AnsiEscape::bg(16));
        }

        $buffer->draw(
            (int)$snugglyX,
            -10,
            $snugglySprite,
            ansiEscape: $this->snugglyColor,
            transparency: '#',
        );
    }
}}
