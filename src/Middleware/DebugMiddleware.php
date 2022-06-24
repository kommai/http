<?php

declare(strict_types=1);

namespace Kommai\Http\Middleware;

use InvalidArgumentException;
use Kommai\Http\Request;
use Kommai\Http\Response;
use Kommai\Http\View\HtmlView;
use Kommai\Http\View\JsonView;

class DebugMiddleware implements MiddlewareInterface
{
    use MiddlewareTrait;

    private float $start;

    public function processRequest(Request $request): Request
    {
        $this->start = microtime(true);
        return $request;
    }

    public function processResponse(Response $response): Response
    {
        $time = sprintf('%.2f ms', (microtime(true) - $this->start) * 1000);
        $memory = sprintf('%d kb', memory_get_peak_usage() / 1024);
        $includes = count(get_included_files());

        if ($response->headers['Content-Type'] === HtmlView::MEDIA_TYPE) {
            $response->body = self::injectDebugHtml($response->body, $time, $memory, $includes, $response->dumps);
        }

        if ($response->headers['Content-Type'] === JsonView::MEDIA_TYPE) {
            $response->body = self::injectDebugJson($response->body, $time, $memory, $includes, $response->dumps);
        }

        return $response;
    }

    private static function express(mixed $data, bool $detail = false): string
    {
        if ($detail) {
            return var_export($data, true);
        }
        return match (gettype($data)) {
            'boolean' => $data ? 'true' : 'false',
            'integer', 'double', 'string' => $data,
            'array' => sprintf('array (%d)', count($data)),
            'object' => get_class($data),
            'NULL' => 'null',
            default => gettype($data),
        };
    }

    private static function generateDebugHtml(
        string $time,
        string $memory,
        int $includes,
        array $dumps = [],
    ): string {
        $html = <<<EOH
    <div id="kommai-debug" style="
        box-sizing: border-box;
        position: fixed;
        left: 0;
        bottom: 0;
        width: 100vw;
        height: 50vh;
        font-family: monospace;
        font-size: 12px;
        color: #222;
        ">
        <div style="
        background: #ccc;
        padding: 8px;
        cursor: pointer;
        ">
EOH;
        $html .= sprintf('&#x23f1; %s &#x1f3cb; %s &#x1f517; %d', $time, $memory, $includes);
        $html .= <<<EOH
        </div>
        <div style="
        overflow-y: scroll;
        background: #eee;
        ">
EOH;
        foreach ($dumps as $dump) {
            $html .= sprintf('<div style="padding: 8px; font-weight: bold;">%s:%d</div>', $dump['file'], $dump['line']);
            $html .= sprintf('<pre style="margin: 0; padding: 0 8px 8px 8px; border-bottom: 1px solid #ccc;">%s</pre>', htmlspecialchars(self::express($dump['data'], $dump['detail'])));
        }
        $html .= <<<EOH
        </div>
    </div>
    <script>
        (() => {
            const CONTAINER_HEIGHT = '50vh';
            const container = document.getElementById('kommai-debug');
            const header = container.children[0];
            const dump = container.children[1];
            const originalBodyMarginBottom = getComputedStyle(document.body).getPropertyValue('margin-bottom');
            document.body.style.marginBottom = 'calc(' + CONTAINER_HEIGHT + ' + ' + originalBodyMarginBottom + ')';
            dump.style.height = 'calc(' + CONTAINER_HEIGHT + ' - ' + header.offsetHeight + 'px)';
            header.addEventListener('click', () => {
                container.style.display = 'none';
                document.body.style.marginBottom = originalBodyMarginBottom;
            });
        })();
    </script>
EOH;
        return $html;
    }

    private static function injectDebugHtml(
        string $targetHtml,
        string $time,
        string $memory,
        int $includes,
        array $dumps = [],
    ): string {
        $position = strripos($targetHtml, '</body>');
        if ($position === false) {
            throw new InvalidArgumentException('Invalid target HTML which is missing </body> tag');
        }
        return substr($targetHtml, 0, $position) . self::generateDebugHtml($time, $memory, $includes, $dumps) . substr($targetHtml, $position);
    }

    private static function injectDebugJson(
        string $targetJson,
        string $time,
        string $memory,
        int $includes,
        array $dumps = [],
    ): string {
        $data = json_decode($targetJson, true);
        if ($data === null) {
            throw new InvalidArgumentException('Invalid target JSON which could be broken');
        }
        $data['debug']['time'] = $time;
        $data['debug']['memory'] = $memory;
        $data['debug']['includes'] = $includes;
        //$data['debug']['dumps'] = $dumps;
        foreach ($dumps as $dump) {
            $data['debug']['dumps'][sprintf('%s:%d', $dump['file'], $dump['line'])] = $dump['data'];
        }
        return json_encode($data);
    }
}
