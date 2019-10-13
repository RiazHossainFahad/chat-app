<?php

namespace App\Http\Controllers;

use App\Events\MessageEvent;
use App\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct()
    {
       $this->middleware("auth"); 
    }

    public function index(){
        return view('chats');
    }

    public function fetchMessages(){
        return Message::with('user')->get();
    }

    public function sendMessage(Request $req){
       $message = auth()->user()->messages()->create([
            'message' => $req->message
        ]);

        broadcast(new MessageEvent($message->load('user')))->toOthers(); 

        return ['status' => 'success'];
    }
}
