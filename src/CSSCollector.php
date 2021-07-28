<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge;

use ICanBoogie\FileCache;

/**
 * Collector for CSS assets.
 */
class CSSCollector extends AssetsCollector
{
    public function __toString()
    {
        $collected = $this->get();

        try {
            if ($this->use_cache) {
                $recent = 0;
                $root = DOCUMENT_ROOT;

                foreach ($collected as $file) {
                    $recent = max($recent, filemtime($root . $file));
                }

                $cache = new FileCache([

                    FileCache::T_REPOSITORY => \ICanBoogie\app()->config['repository.files'] . '/assets',
                    FileCache::T_MODIFIED_TIME => $recent

                ]);

                $key = sha1(implode(',', $collected)) . '.css';

                $rc = $cache->get($key, [ $this, 'cache_construct' ], [ $collected ]);

                if ($rc) {
                    $list = json_encode($collected);

                    return <<<EOT

<link type="text/css" href="{$cache->repository}/{$key}" rel="stylesheet" />

<script type="text/javascript">

var brickrouge_cached_css_assets = $list;

</script>

EOT;
                }
            }
        } catch (\Exception $e) {
            echo render_exception($e);
        }

        #
        # default ouput
        #

        $rc = '';

        foreach ($collected as $url) {
            $rc .= '<link type="text/css" href="' . escape($url) . '" rel="stylesheet" />' . PHP_EOL;
        }

        return $rc;
    }

    /**
     * @inheritdoc
     */
    public function cache_construct(FileCache $cache, $key, array $userdata): string
    {
        list($collected) = $userdata;

        $rc = '/* Compiled CSS file generated by ' . __CLASS__ . ' */' . PHP_EOL . PHP_EOL;

        foreach ($collected as $url) {
            $contents = file_get_contents(DOCUMENT_ROOT . $url);
            $contents = preg_replace('/url\(([^\)]+)/', 'url(' . dirname($url) . '/$1', $contents);

            $rc .= $contents . PHP_EOL;
        }

        file_put_contents(getcwd() . '/' . $key, $rc);

        return $key;
    }
}
