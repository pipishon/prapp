<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vote;

class VoteController extends Controller
{
    public function index (Request $request)
    {
        return Vote::with('order')->orderBy('updated_at', 'desc')->get();
    }

    public function getForm (Request $request)
    {
        $params = $request->only(['hash', 'vote']); //id 100 hash FjAvQe3ev%2FBpEfSK2g%2BANQ%3D%3D
        $order_id = $this->encrypt($params['hash']);
        if (Vote::where('order_id', $order_id)->first() != null) {
            return view('vote', array('repeat' => true));
        }
        $ip = $_SERVER['REMOTE_ADDR'];

        Vote::create(array(
            'order_id' => $order_id,
            'vote' => $request->input('vote'),
            'ip' => $ip
        ));

        return view('vote', $params );
    }

    public function processForm (Request $request)
    {
        $order_id = $this->encrypt($request->input('hash'));
        Vote::where('order_id', $order_id)->update(array(
            'comment' => $request->input('comment')
        ));
        return redirect()->route('vote.success')->with(['vote' => $request->input('vote')]);
    }

    public function getSuccess (Request $request)
    {
        return view('vote_success');
    }

    public function encrypt ($hash)
    {
        $iv = 'p/Ȅ����';
        $hash = rawurldecode($hash);
        return openssl_decrypt($hash, 'AES-128-CBC', 'sercet', 0, $iv);
    }
}
