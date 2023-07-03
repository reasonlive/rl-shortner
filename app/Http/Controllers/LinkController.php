<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Services\EndpointService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class LinkController extends Controller
{
    /** @var int ttl for caching links */
    private const LINK_TTL = 3600; // one hour in seconds

    public function __construct(private readonly EndpointService $endpointService)
    {

    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'link' => ['required', 'regex:/^https?:\/\/.*/iu']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        if ($link = $validator->getData()['link']) {
            // if the link have already been used
            if (DB::table('links')->where('long', $link)->first()) {
                return response()->json(['errors' => ['This URL link have already been shorted']]);
            } else { // save the link to the database
                $links_amount = DB::table('links')->count();
                $endpoint = $this->endpointService->makeEndpoint(608);

                $link_instance = new Link([
                    'long' => $link,
                    'short' => env('APP_URL') . (env('APP_ENV') === 'local' ? ':8000' : '') . '/' . $endpoint,
                    'endpoint' => $endpoint
                ]);

                try {
                    $link_instance->saveOrFail();
                    Cache::put($endpoint, $link, self::LINK_TTL);

                    return response()->json([
                        'links' => DB::table('links')->orderByDesc('created_at')->limit(10)->get()
                    ]);

                } catch (\Throwable $ex) {
                    return response()->json(['errors' => [$ex->getMessage()]]);
                }
            }
        } else {
            return response()->json(['errors' => ['Need to put the link']]);
        }
    }

    public function redirect(string $endpoint): Response
    {
        // link from cache
        if ($link = Cache::get($endpoint)) {
            return redirect($link)->send();
            // link from database
        } elseif ($link = DB::table('links')->where('endpoint', $endpoint)->first()) {
            Cache::put($endpoint, $link->long);

            return redirect($link->long)->send();
        } else {
            return response(status: 404)->send();
        }
    }
}
