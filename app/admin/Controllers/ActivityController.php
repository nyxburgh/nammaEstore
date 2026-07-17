<?php
namespace App\Admin\Controllers;
use App\Core\Middleware;
use App\Admin\Services\ActivityService;

class ActivityController extends AdminController
{
    public function index(): void {
        Middleware::can('activity');
        $f = ['search'=>$this->input('search'),'module'=>$this->input('module'),'actor_type'=>$this->input('actor_type'),'date'=>$this->input('date'),'sort'=>$this->input('sort'),'dir'=>$this->input('dir')];
        $this->view('activity.index',['title'=>'Activity Logs','logs'=>(new ActivityService())->getLogs((int)$this->input('page',1),$f),'filters'=>$f]);
    }
}
