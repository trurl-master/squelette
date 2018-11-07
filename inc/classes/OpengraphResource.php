<?php

namespace Squelette;

use \App;

trait OpengraphResource
{
    public function getOpengraph()
    {
        $custom = $this->getCustom();

        if (!isset($custom['opengraph'])) {
            return false;
        }

        $opengraph = $custom['opengraph'];
        $data = [];

        foreach ($custom['opengraph'] as $og) {

            switch ($og['key']) {
                case 'image':

                    $fn = $og['value'] . '.jpg';

                    $data[] = ['key' => 'og:image', 'value' => $this->resSrcPath() . $fn];

                    list($width, $height) = \getimagesize($this->resPath() . $fn);

                    $data[] = ['key' => 'og:image:width', 'value' => $width];
                    $data[] = ['key' => 'og:image:height', 'value' => $height];

                    break;

                default:
                    $data[] = $og;
                    break;
            }

        }

        return $data;
    }
}
