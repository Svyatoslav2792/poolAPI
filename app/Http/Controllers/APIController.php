<?php

namespace App\Http\Controllers;

use App\Rule;
use App\RuleValues;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class APIController extends Controller
{
    public function postback($url)
    {
        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HEADER, false);
            $out = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);

            if ($out === FALSE) {
                Log::info('cURL Error: ' . curl_error($curl));
                return response()->json('cURL Error: ' . curl_error($curl));
            } elseif (($info['http_code'] < 200)||($info['http_code']>= 300)) {
                Log::info('HTTP Error: ' .$info['http_code']);
                return response()->json('HTTP Error: ' .$info['http_code']);
            }
            Log::info('success! url: '.$url.' response: ' .$out);
            return response()->json(['request: ' => $out, 'response: ' => $url]);
        }
    }

    public function check($url)
    {
        $post_arr = [];
        $input = $url->input();
        $rules = Rule::pluck('title');
        foreach ($input as $key => $value)
        {
            if($rules->contains($key)){
                if(Rule::where('title',$key)->first()->value()->pluck('value')->contains($value)){
                    $post_arr = array_merge($post_arr, RuleValues::where('value',$value)->first()->postback()->pluck('url')->toArray());
                }
            }
        }
        return $post_arr;
    }

    public function getUrlQuery($url, $key = null)
    {
        $parts = parse_url($url);
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
            if (is_null($key)) {
                return $query;
            } elseif (isset($query[$key])) {
                return $query[$key];
            }
        }
        return '';
    }

    public function buildUrls($post_arr, $input_url)
    {
        $completedUrls = [];
        foreach ($post_arr as $post)
        {
            $urlQuery = substr($post,0,strpos($post,'?')+1);
            $postQuery = $this->getUrlQuery($post);
            foreach ($postQuery as $key => $query)
            {
                $postQuery[$key] = $this->getUrlQuery($input_url, $key);
            }
            array_push($completedUrls,$urlQuery.http_build_query($postQuery));
        }
        return $completedUrls;
    }

    public function index(Request $request)
    {
        $req=$this->check($request);
        if(!empty($req)){
            foreach ($this->buildUrls($req,$request->fullUrl()) as $buildUrl)
            {
                $this->postback($buildUrl);
            }
            return 'success';
        } else { return 'empty';}

    }


    public function store(Request $request)
    {
    }

    public function show($id)
    {
    }

    public function update(Request $request, $id)
    {
    }

    public function destroy($id)
    {
    }
}
