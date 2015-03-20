<?php

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

	protected function input()
	{
		return json_decode(file_get_contents("php://input"));
	}

	protected function result($result)
	{
		$result->success = true;

		$data = array(
			"result" => $result,
			"id"     => 1,
			"error"  => null,
		);

		return json_encode($data);
	}

	protected function softError($reason)
	{
		$result = new stdClass;
		$result->success = false;
		$result->error = $reason;

		$data = array(
			"result" => $result,
			"id"     => 1,
			"error"  => null,
		);

		return json_encode($data);
	}

	protected function criticalError($reason, $params = null, $code = 0)
	{
		$error = new stdClass;
		$error->code = $code;
		$error->message = $reason;
		$error->data = $params;

		$data = array(
			"result" => null,
			"id"     => 1,
			"error"  => $error,
		);

		return json_encode($data);
	}

}
