<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class LinkController extends Controller
{
    /** @var int Length of shorter link endpoint */
    private const KEY_LENGTH = 6;

    /** @var int ttl for caching links */
    private const LINK_TTL = 3600; // one hour in seconds
    public function store(Request $request): JsonResponse
    {
        $data = $request->toArray();

        if ($link = $data['long_link'] ?? false) {
            // if the value is not Url link
            if (!preg_match('/^https?:\/\/.*/iu', $data['long_link'])) {
                return response()->json(['error' => 'String must be URL link']);
            }

            // make hash for short link
            $hash = substr(md5($link), 0, self::KEY_LENGTH);

            //$link_from_cache = Cache::get($hash);

            // if link was founded in cache or in database, return the key to front side
            if ($link_instance = Link::query()->firstWhere('link', $link)) {
                return response()->json(['error' => false, 'key' => $link_instance->getKey()]);
            } else { // save the link to the database
                $link_instance = new Link(['link' => $link, 'generated_key' => $hash]);

                try {
                    $link_instance->saveOrFail();
                    Cache::put($hash, $link, self::LINK_TTL);

                    return response()->json([
                        'error' => false,
                        'key'   => $hash
                    ]);

                } catch (\Throwable $ex) {
                    return response(status: 507)->json(['error' => $ex->getMessage()]);
                }
            }
        } else {
            return response()->json(['error' => 'Need to put the link']);
        }
    }

    public function redirect(string $key): Response
    {
        // link as a full long link from the client
        if ($link = Cache::get($key)) {
            return redirect($link)->send();
            // link as a Link::class instance
        } elseif ($link = Link::query()->firstWhere('generated_key', $key)) {
            Cache::put($key, $link->getLink());

            return redirect($link->getLink())->send();
        } else {
            return response(status: 404)->send();
        }
    }
}
