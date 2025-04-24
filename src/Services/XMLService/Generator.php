<?php

namespace Partymeister\Slides\Services\XMLService;

use Illuminate\Support\Arr;
use Motor\Media\Models\File;
use Partymeister\Slides\Models\Playlist;
use Partymeister\Slides\Models\Slide;
use Partymeister\Slides\Services\XMLService;
use SimpleXMLElement;

/**
 * Class Generator
 */
class Generator
{
    /**
     * @return mixed
     */
    public static function siegmeister($parameters)
    {
        $xml = new SimpleXMLElement('<xml></xml>');

        return self::add_encoding($xml->asXML());
    }

    /**
     * @return mixed
     */
    public static function add_encoding($xml)
    {
        return str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="utf-8"?>', $xml);
    }

    /**
     * @return bool|string
     */

    /**
     * @return bool|mixed
     */
    public static function playlist($parameters)
    {
        // Load Playlist
        $playlist = Playlist::find($parameters['playlist_id']);
        if (is_null($playlist)) {
            return false;
        }
        $playlist_items = $playlist->items()
            ->orderBy('sort_position', 'ASC')
            ->get();
        $parameters['date_modified'] = strtotime($playlist->updated_at);

        $xml = new SimpleXMLElement('<xml></xml>');

        $rpc = $xml->addChild('rpc');
        $rpc->addAttribute('type', 'request');
        $rpc->addAttribute('name', 'playlist');

        $param = $xml->addChild('parameter', (int) $parameters['playlist_id']);
        $param->addAttribute('name', 'name');

        // FIXME status url is still from pm2
        $param = $xml->addChild('parameter', config('partymeister-slides.screens_url').'/backend/screens/status');
        $param->addAttribute('name', 'callback');

        // $param = $xml->addChild('parameter', $playlist->type);
        // $param->addAttribute('name', 'name');

        $param = $xml->addChild('parameter', $parameters['date_modified']);
        $param->addAttribute('name', 'timestamp');

        if (Arr::get($parameters, 'callbacks') == 1) {
            $param = $xml->addChild('parameter', 1);
        } else {
            $param = $xml->addChild('parameter', 0);
        }
        $param->addAttribute('name', 'callbacks_active');

        if (isset($parameters['loop'])) {
            $param = $xml->addChild('parameter', (int) $parameters['loop']);
            $param->addAttribute('name', 'loop');
        }

        $data = $xml->addChild('data');

        foreach ($playlist_items as $playlist_item) {
            if ($playlist_item->slide_id != null) {
                $attachment = $playlist_item->slide->getFirstMedia('final');
            } elseif ($playlist_item->file_association != null) {
                $attachment = $playlist_item->file_association->file->getFirstMedia('file');
            }

            $item = $data->addChild('item');
            $item->addAttribute('name', $parameters['playlist_id'].'_'.$playlist_item->id);

            if ($playlist_item->type == 'now' || $playlist_item->type == 'end' || $playlist_item->type == 'comingup') {
                // FIXME: this does not happen
                $faketype = 'image';
            } else {
                $faketype = $playlist_item->type;
            }

            if ($playlist_item->slide_type == 'siegmeister_winners' || $playlist_item->slide_type == 'siegmeister_bars') {
                $faketype = $playlist_item->slide_type;
            }

            $item->addAttribute('type', $faketype);

            // Add callback if available
            if ((string) $playlist_item->callback_hash != '') {
                $callback = $item->addChild('callback', config('partymeister-slides.screens_url').'/api/callback/'.$playlist_item->callback_hash);
                $callback->addAttribute('delay', $playlist_item->callback_delay.'.0');
            }

            // Hack for type = web
            // if ($playlist_item->type == 'web')
            // {
            //	$url = str_replace('URL:', '', $attachment->file->description);
            //	$item->addChild('path', $url);
            // }
            // else
            // {
            //	$item->addChild('path', config('partymeister-slides.screens_url').$attachment->file->getUrl() . $attachment->file->filename);
            // }
            if ($playlist_item->slide_id != null) {
                $item->addChild('path', config('partymeister-slides.screens_url').route('backend.slides.show', [$playlist_item->slide->id], false));
            } else {
                $item->addChild('path', config('partymeister-slides.screens_url').$attachment->getUrl());
            }

            if ($playlist_item->type == 'siegmeister_bars') {
                $item->addChild('duration', 8);
            } elseif ($playlist_item->type == 'siegmeister_winners') {
                $item->addChild('duration', 0);
            } else {
                $item->addChild('duration', $playlist_item->duration);
            }

            $item->addChild('midi', $playlist_item->midi_note);

            if ($playlist_item->slide_type != '') {
                $item->addChild('slide_type', $playlist_item->slide_type);
            } else {
                $item->addChild('slide_type', $playlist_item->type);
            }

            if ($playlist_item->transition != null) {
                $transition = $item->addChild('transition', $playlist_item->transition->identifier);
            } else {
                $transition = $item->addChild('transition', 255);
            }
            $transition->addAttribute('duration', $playlist_item->transition_duration);

            $item->addChild('manual_advance', $playlist_item->is_advanced_manually);
            $item->addChild('mute', $playlist_item->is_muted);

            if ($playlist_item->slide_type == 'siegmeister_bars' || $playlist_item->slide_type == 'siegmeister_winners') {
                $siegmeister = $item->addChild('siegmeister');
                $siegmeister->addChild('bar_color', config('partymeister-slides-prizegiving.bar_color'));
                $siegmeister->addChild('bar_blink_color_1', config('partymeister-slides-prizegiving.bar_blink_color_1'));
                $siegmeister->addChild('bar_blink_color_2', config('partymeister-slides-prizegiving.bar_blink_color_2'));
                $siegmeister->addChild('bar_alpha', config('partymeister-slides-prizegiving.bar_alpha'));

                $metadata = json_decode($playlist_item->metadata);

                if ($metadata && is_array($metadata)) {
                    foreach ($metadata as $entry) {
                        $bar = $siegmeister->addChild('bar');
                        $bar->addAttribute('x1', $entry->x1);
                        $bar->addAttribute('x2', $entry->x2);
                        $bar->addAttribute('y1', $entry->y1);
                        $bar->addAttribute('y2', $entry->y2);
                    }
                } elseif (isset($previousItem)) {
                    $metadata = json_decode($previousItem->metadata);
                    if ($metadata && is_array($metadata)) {
                        foreach ($metadata as $entry) {
                            $bar = $siegmeister->addChild('bar');
                            $bar->addAttribute('x1', $entry->x1);
                            $bar->addAttribute('x2', $entry->x2);
                            $bar->addAttribute('y1', $entry->y1);
                            $bar->addAttribute('y2', $entry->y2);
                        }
                    }
                }
            }
            $previousItem = $playlist_item;
        }
        if (Arr::get($_GET, 'debug')) {
            print_r(self::add_encoding($xml->asXML()));
            exit();
        }

        return self::add_encoding($xml->asXML());
    }

    /**
     * @return bool|mixed
     */

    /**
     * @return bool|string
     */
    public static function playnow($parameters)
    {
        if (isset($parameters['slide_id'])) {
            $parameters = [
                'playnow' => true,
                'playlist_id' => 'playnow_slide_'.$parameters['slide_id'],
                'slide_id' => $parameters['slide_id'],
                'date_modified' => time(),
                'loop' => 0,
            ];
            $slide = Slide::find($parameters['slide_id']);
            $attachment = $slide->getFirstMedia('final');
        } else {
            $parameters = [
                'playnow' => true,
                'playlist_id' => 'playnow_file_'.$parameters['file_id'],
                'slide_id' => $parameters['file_id'],
                'date_modified' => time(),
                'loop' => 0,
            ];
            $file = File::find($parameters['slide_id']);
            $attachment = $file->getFirstMedia('file');
        }

        $xml = new SimpleXMLElement('<xml></xml>');

        $rpc = $xml->addChild('rpc');
        $rpc->addAttribute('type', 'request');
        $rpc->addAttribute('name', 'playlist');

        $param = $xml->addChild('parameter', $parameters['playlist_id']);
        $param->addAttribute('name', 'name');

        $param = $xml->addChild('parameter', $parameters['date_modified']);
        $param->addAttribute('name', 'timestamp');

        if (isset($parameters['loop'])) {
            $param = $xml->addChild('parameter', (int) $parameters['loop']);
            $param->addAttribute('name', 'loop');
        }

        $data = $xml->addChild('data');

        $item = $data->addChild('item');
        $item->addAttribute('name', $parameters['playlist_id'].'_'.$parameters['slide_id']);

        if (in_array($attachment->mime_type, config('motor-backend-mimetypes.video'))) {
            $item->addAttribute('type', 'video');
        } elseif (in_array($attachment->mime_type, config('motor-backend-mimetypes.image'))) {
            $item->addAttribute('type', 'image');
        }

        // $item->addChild('path', config('partymeister-slides.screens_url') . $attachment->getUrl());
        if (isset($slide) && ! is_null($slide)) {
            $item->addChild('path', config('partymeister-slides.screens_url').route('backend.slides.show', [$slide->id], false));
        } else {
            $item->addChild('path', config('partymeister-slides.screens_url').$attachment->getUrl());
        }
        $item->addChild('duration', '2000');

        $slide_type = 'announce';
        if (isset($slide)) {
            $slide_type = $slide->slide_type;
        }
        $item->addChild('slide_type', $slide_type);

        $transition = $item->addChild('transition', 255);
        $transition->addAttribute('duration', 2000);
        // $transition->addAttribute('duration', $playlist_item->transition_duration);

        $item->addChild('manual_advance', 1);

        $xml = self::add_encoding($xml->asXML());

        XMLService::_send($xml);

        $xml = self::seek([
            'playlist_id' => $parameters['playlist_id'],
            'slide_id' => $parameters['slide_id'],
        ]);

        return XMLService::_send($xml);
    }

    /**
     * @return mixed
     */

    /**
     * @return bool|mixed
     */
    public static function seek($parameters)
    {
        // If no slide id is given - get the first slide from the playlist
        if (! isset($parameters['slide_id'])) {
            $playlist = Playlist::find($parameters['playlist_id']);
            if ($playlist == null) {
                return false;
            }
            $playlist_item = $playlist->items()
                ->orderBy('sort_position', 'ASC')
                ->first();
            $parameters['slide_id'] = $playlist_item->id;
        }

        if (! isset($parameters['hard'])) {
            $parameters['hard'] = 0;
        }

        $xml = new SimpleXMLElement('<xml></xml>');

        $rpc = $xml->addChild('rpc');
        $rpc->addAttribute('type', 'request');
        $rpc->addAttribute('name', 'seek');

        $param = $xml->addChild('parameter', $parameters['playlist_id']);
        $param->addAttribute('name', 'playlist');

        $param = $xml->addChild('parameter', $parameters['playlist_id'].'_'.$parameters['slide_id']);
        $param->addAttribute('name', 'slide');

        $param = $xml->addChild('parameter', (int) $parameters['hard']);
        $param->addAttribute('name', 'hard');

        return self::add_encoding($xml->asXML());
    }

    /**
     * @return mixed
     */

    /**
     * @return mixed
     */
    public static function previous($parameters)
    {
        $xml = new SimpleXMLElement('<xml></xml>');

        $rpc = $xml->addChild('rpc');
        $rpc->addAttribute('type', 'request');
        $rpc->addAttribute('name', 'previous');

        $param = $xml->addChild('parameter', (int) $parameters['hard']);
        $param->addAttribute('name', 'hard');

        return self::add_encoding($xml->asXML());
    }

    /**
     * @return mixed
     */

    /**
     * @return mixed
     */
    public static function next($parameters)
    {
        $xml = new SimpleXMLElement('<xml></xml>');

        $rpc = $xml->addChild('rpc');
        $rpc->addAttribute('type', 'request');
        $rpc->addAttribute('name', 'next');

        $param = $xml->addChild('parameter', (int) $parameters['hard']);
        $param->addAttribute('name', 'hard');

        return self::add_encoding($xml->asXML());
    }

    /**
     * @return mixed
     */

    /**
     * @return mixed
     */
    public static function get_playlists($parameters)
    {
        $xml = new SimpleXMLElement('<xml></xml>');
        $rpc = $xml->addChild('rpc');
        $rpc->addAttribute('type', 'request');
        $rpc->addAttribute('name', 'get_playlists');

        return self::add_encoding($xml->asXML());
    }

    /**
     * @param  $xml
     * @return mixed
     */

    /**
     * @return mixed
     */
    public static function get_system_info($parameters)
    {
        $xml = new SimpleXMLElement('<xml></xml>');
        $rpc = $xml->addChild('rpc');
        $rpc->addAttribute('type', 'request');
        $rpc->addAttribute('name', 'get_system_info');

        return self::add_encoding($xml->asXML());
    }
}
