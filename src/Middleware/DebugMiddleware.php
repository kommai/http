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
    private float $end;

    public function processRequest(Request $request): Request
    {
        $this->start = microtime(true);
        return $request;
    }

    public function processResponse(Response $response): Response
    {
        $this->end = microtime(true);

        if ($response->headers['Content-Type'] === HtmlView::MEDIA_TYPE) {
            $response->body = $this->injectDebugHtml($response->body, $this->generateDebugHtml(
                $response,
                ($this->end - $this->start) * 1000,
                (int) floor(memory_get_peak_usage() / 1024),
                count(get_included_files())
            ));
        }

        if ($response->headers['Content-Type'] === JsonView::MEDIA_TYPE) {
            // TODO: what to do with JSON?
        }

        return $response;
    }

    private function generateDebugHtml(Response $response, float $time, int $memory, int $includes): string
    {
        //return '<div id="kommai-debug"></div>';
        $html = <<<EOH
<div id="kommai-debug" style="
        box-sizing: border-box;
        position: fixed;
        left: 0;
        bottom: 0;
        width: 100vw;
        height: 50vh;
        background: #333;
        font-family: monospace;
        font-size: 12px;
        ">
        <div style="
        background: #ccc;
        padding: 8px;
        ">
EOH;
        $html .= sprintf('⏱️ %.2f ms 🏋️ %d kb 🔗 %d', $time, $memory, $includes);
        $html .= <<<EOH
        </div>
        <div style="
        overflow-y: scroll;
        background: #999;
        ">
EOH;
        foreach ($response->debug as $debug) {
            $html .= sprintf('<div style="padding: 8px;">%s:%d</div>', $debug['file'], $debug['line']);
            $html .= sprintf('<pre style="margin: 0; padding: 0 8px 8px 8px; border-bottom: 1px solid #ccc;">%s</pre>', $debug['dump']);
        }
        $html .= <<<EOH
        </div>
    </div>
    <script>
        console.log(getComputedStyle(document.body).getPropertyValue('margin-top'));
        const debug = document.getElementById('kommai-debug');
        //document.body.style.marginBottom = 'calc(' + getComputedStyle(debug).getPropertyValue('height') + ' + ' + parseInt(getComputedStyle(document.body).getPropertyValue('margin-bottom')) + 'px)';
        document.body.style.marginBottom = 'calc(50vh + ' + parseInt(getComputedStyle(document.body).getPropertyValue('margin-bottom')) + 'px)';
        const debugHeader = debug.children[0];
        //console.log(debugHeader.offsetHeight);
        const debugDump = debug.children[1];
        console.log(debugDump);
        debugDump.style.height = 'calc(50vh - ' + debugHeader.offsetHeight + 'px)';
        console.log('calc(50vh - ' + debugHeader.offsetHeight + 'px)');
    </script>
EOH;
        return $html;
    }

    private function injectDebugHtml(string $sourceHtml, string $debugHtml): string
    {
        $position = strripos($sourceHtml, '</body>');
        if ($position === false) {
            throw new InvalidArgumentException('Invalid source HTML which is missing </body> tag');
        }
        return substr($sourceHtml, 0, $position) . $debugHtml . substr($sourceHtml, $position);
    }

    private function injectDebugJson(string $sourceJson): string
    {
        // TODO: do stuff
        return '';
    }
}
