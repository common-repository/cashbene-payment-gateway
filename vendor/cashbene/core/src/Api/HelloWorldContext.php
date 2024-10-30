<?php

namespace Cashbene\Core\Api;

class HelloWorldContext extends Context {
	public function getHelloWorld()
	{
//        ValidatorService::validate(['message' => 'Hello World!']);
		return ['message' => 'Hello World!'];
	}

    public function getFailure()
    {
//        ValidatorService::validate(['message' => 'Woops, something wrong!', 'code' => 'POINT_ID_MISSING']);
        return ['message' => 'Hello World!'];
    }


    public function submitHelloWorld(array $data)
	{
		return [
			'data' => $data,
			'status' => 'success',
			'test' => 'Hello World!'
		];
	}

}
