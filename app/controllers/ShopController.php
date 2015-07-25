<?php

class ShopController extends \BaseController {

    public function shop()
    {
        if (Auth::guest())
        {
            return $this->softError("Not logged");
        }

        $req = $this->input();

        if (@$req->params[0] != "DOFUS_INGAME")
        {
            return $this->softError("KEYUNKNOWN");
        }

            if (@$req->method == "Home")         return $this->Home();
        elseif (@$req->method == "ArticlesList") return $this->ArticlesList();
        elseif (@$req->method == "QuickBuy")     return $this->QuickBuy();
        else return $this->criticalError("Method not found");
    }

    private function Home()
    {
        $req = $this->input();

        $result = new stdClass;
        $result->content = $this->welcome();
        return $this->result($result);
    }

    private function ArticlesList()
    {
        $req = $this->input();
        $categoryId = @$req->params[2];

        if (@$categoryId)
        {
            $result = $this->page($categoryId);
            return $this->result($result);
        }
        else
        {
            return Home();
        }
    }

    private function QuickBuy()
    {
        $req = $this->input();
        $itemId = @$req->params[2];

        if (@$itemId)
        {
            return $this->buy($itemId);
        }
        else
        {
            return $this->criticalError("invalid item id param");
        }
    }

    private function welcome()
    {
        $content = new stdClass;

        $content->categories = $this->categories();
        $content->gondolahead_main = $this->gondolahead_main();
        $content->gondolahead_article = $this->gondolahead_article();
        $content->hightlight_carousel = $this->hightlight_carousel();
        $content->hightlight_image = $this->hightlight_image();

        return $content;
    }

    private function page($id)
    {
        $content = new stdClass;

        $categories = json_decode(file_get_contents("SHOP/categories.json"))->categories;

        $key = null;
        foreach ($categories as $category)
        {
            if ($id == $category->id)
            {
                $key = $category->key;
                break;
            }
        }

        if ($key != null)
        {
            $files = scandir("SHOP/$key");
            $content->result = true;
            $content->count = 0;
            $content->articles = array();

            foreach ($files as $file)
            {
                if (in_array($file, array(".", "..")))
                    continue;

                $content->articles[] = json_decode(file_get_contents("SHOP/$key/$file"));
                $content->count++;
            }

            return $content;
        }
        else
        {
            return $this->criticalError("Categorie not found");
        }
    }

    private function buy($itemId)
    {
        if ($itemId < 100)
        {
            return $this->criticalError("Special item not impleted");
        }

        // TODO: search item by id

        $price = 6000;

        if (Auth::user()->Tokens < $price)
        {
            return $this->criticalError("Not enought Ogrines");
        }

        $buyRequest = new stdClass;
        $buyRequest->key = "ILovePanda";
        $buyRequest->price = 0;
        $buyRequest->characterId = Session::get('characterId');
        $buyRequest->actions = array();

        $action = new stdClass;
        $action->type = "item";
        $action->item = new stdClass;
        $action->item->itemId = $itemId;
        $action->item->quantity = 1;
        $action->item->maxEffects = false;

        $buyRequest->actions[] = $action;

        $request = json_encode($buyRequest);

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($socket === false)
        {
            return $this->criticalError("unable to contact shop server: " . socket_strerror(socket_last_error()));
        }

        $result = socket_connect($socket, gethostbyname("voidmx.net"), 7002);

        if ($socket === false)
        {
            return $this->criticalError("unable to contact shop server: ($result) " . socket_strerror(socket_last_error()));
        }

        socket_write($socket, $request, strlen($request));
        socket_close($socket);

        Auth::user()->update(array(
			'Tokens' => Auth::user()->Tokens - $price,
		));

        return $this->criticalError("not implented");
    }

    private function categories()
    {
        $data = new stdClass;

        $data->result = true;
        $data->categories = array();
        $data->categories[] = $this->categories_categorie(327, "SHOP_HOME", "Accueil");

        return $data;
    }

    private function categories_categorie($id, $key, $name)
    {
        $categorie = new stdClass;

        $categorie->id = $id;
        $categorie->key = $key;
        $categorie->name = $name;
        $categorie->displaymode = "MOSAIC";
        $categorie->description = "";
        $categorie->image = false;
        $categorie->child = array();
        //$categorie->child[] = $this->categories_categorie_child(1, "Familiers");

        $categories = json_decode(file_get_contents("SHOP/categories.json"))->categories;

        foreach ($categories as $category)
        {
            $categorie->child[] = $this->categories_categorie_child(
                $category->id,
                $category->name
            );
        }

        return $categorie;
    }

    private function categories_categorie_child($id, $name)
    {
        $child = new stdClass;

        $child->id = $id;
        $child->key = null;
        $child->name = $name;
        $child->displaymode = "LIST";
        $child->description = "";
        $child->image = false;

        return $child;
    }

    private function gondolahead_main()
    {
        $data = new stdClass;

        $data->result = true;
        $data->gondolaheads = array();

        return $data;
    }

    private function gondolahead_article()
    {
        return json_decode(file_get_contents("SHOP/0_HOME/gondolahead_article.json"));;
    }

    private function hightlight_carousel()
    {
        return json_decode(file_get_contents("SHOP/0_HOME/hightlight_carousel.json"));;
    }

    private function hightlight_image()
    {
        return json_decode(file_get_contents("SHOP/0_HOME/hightlight_image.json"));;
    }

}
