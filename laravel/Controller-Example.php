<?php

/**
 * @file
 * The page controller manages pages in the CMS system
 *
 * It defines the methods that are protected by authentication middleware
 * it shows the default home view when no other route is chosen
 * it shows the proper page when a valid slug has been requested 
 * it defines restful resources for updating pages through the admin ui
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Page;
use App\Content;
use App\Variables;

class PageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page = new \stdClass;
        $page->title = '';
        $page->slug = '';
        $page->meta_description = '';
        return view('admin.page.create', compact('page'));
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //stringify the slug into proper url encoding
        $slug = str_slug($request->slug);
        //save to database
        $page = Page::create(['slug' => $slug])
            ->contents()->create(request(['lang','title','meta_description']));
        //redirect to the newly created page
        return redirect($slug);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        //load the page we want from the database
        $page = DB::table('pages')
            ->join('contents', function ($join) {
                $join->on('pages.id', '=', 'contents.page_id')->where('contents.lang', '=', "en");
            })
            ->where('pages.slug', '=', $slug)
            ->first();
        
        //early exit if we have no content
        if(empty($page->content))
            return view('admin.page.edit', ['page' => $page, 'contents' => array()]);

        //create a collection of data from the page content
        $collection = collect( unserialize($page->content) )
            ->map(function ($item, $key) {
            
                if(!isset($item['order']))
                    $item['order'] = 0;

                if(!isset($item['type']))
                    $item['type'] = 'content';

                return $item;
            })
            ->sortBy('order');

        //return the view
        return view('admin.page.edit', ['page' => $page, 'contents' => $collection]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$slug)
    {
        //get the page model
        $page = Page::where('slug',$slug)->first();
        //upage the page model
        $page->update(request(['slug']));
        //update the content model
        $content = Content::where([['page_id',$page->id],['lang','en']])->update(request(['title','meta_description']));
        //return the user to the page
        return redirect($request->slug);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        //load the variables model
        $template = Variables::where('name','template')->first();

        //load the page from the DB
        //faster to load from query builder than go through the ORM
        $page = DB::table('pages')
            ->join('contents', function ($join) {
                $join->on('pages.id', '=', 'contents.page_id')->where('contents.lang', '=', "en");
            })
            ->where('pages.slug', '=', $slug)
            ->first();
        
        //define body variable
        $body = '';

        //early exit if no page content
        if(empty($page->content))
            return view("themes.{$template->value}.page", ['page' => $page, 'body' => $body]);

        //unserialize the content from the data store
        $pageContent = unserialize($page->content);

        //create an sections collection from the page content
        $sections = collect($pageContent)->where('display', 1);

        //render a section into the body for each section in sections 
        $sections->each(function ($item, $key) use (&$body) {
            //load the correct view for each section
            $view = View::make("sections.{$item['type']}.{$item['style']}", ['data' => $item['data']] );
            //render to the body variable
            $body .= $view->render();
        });
        
        //return the page to the user
        return view("themes.{$template->value}.page", ['page' => $page, 'body' => $body]);
    }


}
