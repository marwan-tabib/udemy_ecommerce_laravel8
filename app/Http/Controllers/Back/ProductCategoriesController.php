<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Http\Requests\Back\ProductCategoryRequest;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ProductCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        // keyword
        // status
        // sort_by
        // order_by
        // limit_by

        $categories = ProductCategory::withCount('products')
            ->when(\request()->keyword != null , function($query){
                $query->search(\request()->keyword);
            })
            ->when(\request()->status != '' , function($query){
                $query->whereStatus(\request()->status);
            })
            ->orderBy(\request()->sort_by ?? 'id' , \request()->order_by ?? 'desc')
            ->paginate(\request()->limit_by ?? 10);
        return view('back.product_categories.index' , compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $main_categories = ProductCategory::whereNull('parent_id')->get(['id' , 'name']);
        return view('back.product_categories.create' , compact('main_categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ProductCategoryRequest $request)
    {
        $input['name'] = $request->name;
        $input['status'] = $request->status;
        $input['parent_id'] = $request->parent_id;
        if($image = $request->file('cover')){
            $file_name = Str::slug($request->name)."-".time().".".$image->getClientOriginalExtension();
            $path = public_path('assets\product_categories' . $file_name);
            Image::make($image->getRealPath())->resize(500, null , function ($constraint){
                $constraint->aspectRatio();
            })->save($path, 100);
            $input['cover'] = $file_name;
        }

        ProductCategory::create($input);
        return redirect()->route('admin.product_categories.index')->with([
            'message' => 'Created successfully',
            'alert-type'=> 'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('back.product_categories.show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('back.product_categories.edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
