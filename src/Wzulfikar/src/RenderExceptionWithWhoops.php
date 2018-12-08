<?php

namespace Wzulfikar\WhoopsTrait;

use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Util\Misc;

trait RenderExceptionWithWhoops
{
    private function handleExceptionWithWhoops(\Exception $e, $editor = null)
    {
        $isAjax = Misc::isAjaxRequest();
        $handler = $isAjax ? new JsonResponseHandler : new PrettyPageHandler;
        if (!$isAjax && $editor) {
            // https://github.com/filp/whoops/blob/master/docs/Open%20Files%20In%20An%20Editor.md
            $handler->setEditor($editor);
        }
        if (Misc::isCommandLine()) {
            $handler = new PlainTextHandler;

            // disable trace in command line & only display error message
            $handler->addTraceToOutput(false);
        }

        $whoops = new \Whoops\Run;
        $whoops->pushHandler($handler);
        return $whoops->handleException($e);
    }

    private function renderExceptionWithWhoops(\Exception $e, $editor = null)
    {
        $handledException = $this->handleExceptionWithWhoops($e, $editor);
        
        $responseClass = 'Symfony\Component\HttpFoundation\Response';
        if (class_exists($responseClass)) {
            return new $responseClass(
                $handledException,
                $e->getStatusCode(),
                $e->getHeaders()
            );
        }
        return $handledException;
    }
}
