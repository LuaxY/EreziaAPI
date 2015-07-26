<?php

class ShopController extends \BaseController {

    public function shop()
    {
        if (Auth::guest())
        {
            $data = new stdClass;
            $data->error = "AUTH_FAILED";
            return $this->result($data);
        }

        $req = $this->input();

        if (@$req->params[0] != "DOFUS_INGAME")
        {
            return $this->softError("KEY_UNKNOWN");
        }

            if (@$req->method == "Home")         return $this->Home();
        elseif (@$req->method == "ArticlesList") return $this->ArticlesList();
        elseif (@$req->method == "QuickBuy")     return $this->QuickBuy();
        else return $this->softError("Method not found");
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

        if ($categoryId)
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

        if ($itemId)
        {
            $result = $this->buy($itemId);
            return $this->result($result);
        }
        else
        {
            return $this->softError("invalid item id param");
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

    private function page($categorieId)
    {
        $content = new stdClass;

        $content->result = true;
        $content->count = 0;
        $content->articles = array();

        $objects = Object::where('categorie_id', $categorieId)->where('enabled', 1)->get();

        foreach ($objects as $object)
        {
            $currentObject = new stdClass;

            $currentObject->id =             "{$object->id}";
            $currentObject->key =            $object->key;
            $currentObject->name =           $object->name;
            $currentObject->subtitle =       $object->subtitle;
            $currentObject->description =    $object->description;

            if ($object->promo < 0)
            {
                $currentObject->price =          $object->price + $object->promo;
                $currentObject->original_price = $object->price;
            }
            else
            {
                $currentObject->price =          $object->price;
                $currentObject->original_price = null;
            }

            $currentObject->startdate =      $object->startdate;
            $currentObject->enddate =        null;
            $currentObject->currency =       "OGR";
            $currentObject->stock =          null;
            $currentObject->image =          new stdClass;

            $currentObject->image->{'70_70'}   = false;
            $currentObject->image->{'200_200'} = false;
            $currentObject->image->{'590_178'} = false;

            $currentObject->references =     array();

            $reference = new stdClass;

            $reference->type        = "VIRTUALGIFT";
            $reference->quantity    = "{$object->quantity}";
            $reference->free        = 0;
            $reference->name        = $object->name;
            $reference->description = $object->description;
            $reference->content     = array();

            $item = new stdClass;

            $item->id          = $object->item->Id;
            $item->name        = $object->name;
            $item->description = $object->description;
            $item->image       = false;

            $reference->content[] = $item;

            $currentObject->references[] = $reference;

            $content->articles[] = $currentObject;
            $content->count++;
        }

        return $content;
    }

    private function buy($objectId)
    {
        $content = new stdClass;

        $object = Object::find($objectId);

        if (!$object)
        {
            $content->error = "PAIDFAILED";
            return $content;
        }

        $price = $object->price + $object->promo;

        if (Auth::user()->Tokens < $price)
        {
            $content->error = "MISSINGMONEY";
            return $content;
        }

        $buyRequest = new stdClass;
        $buyRequest->key = "ILovePanda";
        $buyRequest->price = $price;
        $buyRequest->characterId = Session::get('characterId');
        $buyRequest->actions = array();

        $action = new stdClass;
        $action->type = "item";
        $action->item = new stdClass;
        $action->item->itemId = $object->item_id;
        $action->item->quantity = 1;
        $action->item->maxEffects = false;

        $buyRequest->actions[] = $action;

        $request = json_encode($buyRequest);

        if (($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false)
        {
            return $this->criticalError("unable to contact shop server: " . socket_strerror(socket_last_error()));
        }

        if (!socket_connect($socket, Config::get('dofus.shop_host'), Config::get('dofus.shop_port')))
        {
            return $this->criticalError("unable to contact shop server: " . socket_strerror(socket_last_error()));
        }

        socket_write($socket, $request, strlen($request));
        socket_close($socket);

        Auth::user()->Tokens -= $price;
        Auth::user()->update(array('Tokens' => Auth::user()->Tokens));

        $content->result = true;
        $content->ogrins = Auth::user()->Tokens;
        $content->krozs = 0;

        return $content;
    }

    private function categories()
    {
        $data = new stdClass;

        $data->result = true;
        $data->categories = array();
        $data->categories[] = $this->categories_categorie(327, "SHOP_HOME", "Boutique Erezia");

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

        $childs = Category::where('enabled', 1)->get();

        foreach ($childs as $child)
        {
            if ($child->parent == 0)
                $categorie->child[] = $this->categories_categorie_child($child);
        }

        return $categorie;
    }

    private function categories_categorie_child($currentChild)
    {
        $categorie = new stdClass;

        $categorie->id = $currentChild->id;
        $categorie->key = $currentChild->key;
        $categorie->name = $currentChild->name;
        $categorie->displaymode = $currentChild->displaymod;
        $categorie->description = $currentChild->description;
        $categorie->image = $currentChild->image;
        $categorie->child = array();

        //$childs = $childs = $currentChild->childs;
        $childs = Category::where('parent', $currentChild->id)->where('enabled', 1)->get();

        foreach ($childs as $child)
        {
            $categorie->child[] = $this->categories_categorie_child($child);
        }

        return $categorie;
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
