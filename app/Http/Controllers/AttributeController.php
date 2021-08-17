<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Models\AttributeGroup;
use App\Http\Requests\AttributeStoreRequest;
use App\Http\Requests\AttributeUpdateRequest;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $attributes = Attribute::where('group_id', $request->group)->get();

        return response()->json([
            "status" => "success",
            "data" => [
                "attributes" => $attributes
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\AttributeStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AttributeStoreRequest $request)
    {
        $this->authorize('create', Attribute::class);

        $attribute = Attribute::create($request->validated());

        return response()->json([
            'data' => [
                'attribute' => $attribute
            ]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function show(Attribute $attribute)
    {
        return response()->json([
            "status" => "success",
            "data" => [
                "attribute" => $attribute,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\AttributeUpdateRequest  $request
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function update(AttributeUpdateRequest $request, Attribute $attribute)
    {
        $this->authorize('update', $attribute);

        $attribute->update($request->validated());

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'attribute' => $attribute->refresh()
                ]
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attribute $attribute)
    {
        $this->authorize('delete', $attribute);

        $attribute->delete();

        return response()->json(
            [
                'status' => 'success'
            ]
        );
    }
}
