<?php

namespace App\Http\Controllers\Demo;

use App\Contracts\Demo\FirstInterface;

use App\Services\Demo\First;

use App\Http\Controllers\Controller;
use App\Jobs\SendMessage;

class FirstController extends Controller
{
    /**
     * 测试：user实例
     *
     * @var Hello
     */
    protected $user;

    /**
     * Create a new controller instance.
     * 创造一个FirstInterface实例
     *
     * @param  FirstInterface $user
     * @return void
     */
    public function __construct(FirstInterface $user){
        $this->user = $user;
    }

    /**
     * Display a list of all of the user.
     *
     * @api            {get} api/demo
     * @apiName        测试
     * @apiStatus      todo
     * @apiDescription 测试
     * @apiVersion     1.0.0
     * @apiSuccess {integer} data.index 用户ID
     * @apiSuccess {string} data.username 用户名称
     */
    public function index()
    {
        return response()->json([
            'user_id' => 1,
            'user_name' => $this->user->getUsername(),
        ]);
    }

    /**
     * Display a list of all of the user2.
     * @api            {get} demo2
     * @apiName        测试
     * @apiStatus      todo
     * @apiDescription 测试
     * @apiVersion     1.0.0
     * @apiSuccess {integer} data.index 用户ID
     * @apiSuccess {string} data.username 用户名称
     * @return Response
     */
    public function index2(First $user)
    {
        return response()->json([
            'status' => 200,
            'message' => 'success',
            'data' => [
                'user_id' => 2,
                'user_name' => $this->user->getUsername(),
            ]
        ]);
    }


    /**
     * Display a list of all of the user3.
     *
     * @return Response
     */
    public function index3(First $user)
    {
        SendMessage::dispatch('发送消息:'.time());

        return view('demo.index', [
            'user_id' => '3',
            'user_name'=>$this->user->getUsername(),
        ]);
    }
}
