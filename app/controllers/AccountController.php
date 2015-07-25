<?php

class AccountController extends \BaseController {

    public function auth()
    {
        $req = $this->input();

        if ($req->method == "Authentification")
        {
            $result = new stdClass;

            $ticket =      $req->params[0];
            $serverId =    $req->params[1];
            $characterId = $req->params[2];

            if (Auth::check())
                $user = Auth::user();
            else
                $user = Account::where('Ticket', $ticket)->first();

            if ($user)
            {
                if (Auth::guest())
                    Auth::login($user);

                Session::put("ticket",      $ticket);
                Session::put("serverId",    $serverId);
                Session::put("characterId", $characterId);

                $result->nickname = $user->Nickname;
            }
            else
            {
                $result->error = "AUTH_FAILED";
            }

            return $this->result($result);
        }

        return $this->softError("Method not found");
    }

    public function info()
    {
        if (Auth::guest())
        {
            $data = new stdClass;
            $data->error = "AUTH_FAILED";
            return $this->result($data);
        }

        $req = $this->input();

        if (@$req->method == "Money")
        {
            $result = new stdClass;
            $result->ogrins = Auth::user()->Tokens;
            $result->krozs = 0;
            return $this->result($result);
        }

        return $this->softError("Method not found");
    }

}
