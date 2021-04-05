<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;

class Index extends BaseController
{
    public function index()
    {
        return View::fetch();
    }

    public function hello($name = 'ThinkPHP6')
    {
        return app()->getThinkPath();
    }

    public function decode()
    {
        $str = '2["test",{"room","room"}]';
        $i = 0;

        $packet = [];
        $packet['type'] = substr($str, 0, 1);

        // look up namespace (if any)
        if ('/' === substr($str, $i + 1, 1)) {
            $nsp = '';
            while (++$i) {
                $c = substr($str, $i, 1);
                if (',' === $c) {
                    break;
                }
                $nsp .= $c;
                if ($i === strlen($str)) {
                    break;
                }
            }
            $packet['nsp'] = $nsp;
        } else {
            $packet['nsp'] = '/';
        }

        // look up id
        $next = substr($str, $i + 1, 1);
        if ('' !== $next && is_numeric($next)) {
            $id = '';
            while (++$i) {
                $c = substr($str, $i, 1);
                if (null == $c || !is_numeric($c)) {
                    --$i;
                    break;
                }
                $id .= substr($str, $i, 1);
                if ($i === strlen($str)) {
                    break;
                }
            }
            $packet['id'] = intval($id);
        }

        // look up json data
        if (substr($str, ++$i, 1)) {
            $packet['data'] = json_decode(substr($str, $i), true);
        }

        return json_encode($packet);
    }
}
