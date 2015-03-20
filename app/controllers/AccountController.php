<?php

class AccountController extends \BaseController {

    public function auth()
    {
        $req = $this->input();

        if ($req->method == "Authentification")
        {
            // Token, Server ID, Character ID
            if ($req->params[0] == "ADMIN")
            {
                $result = new stdClass;
        		$result->nickname = "Luax";
                return $this->result($result);
            }
            else
                return $this->softError("KEYUNKNOWN");
        }

        return $this->criticalError("Method not found");
    }

    public function info()
    {
        $req = $this->input();

        if ($req->method == "Money")
        {
            $result = new stdClass;
            $result->ogrins = 666;
            $result->krozs = 0;
            return $this->result($result);
        }

        return $this->criticalError("Method not found");
    }

}
