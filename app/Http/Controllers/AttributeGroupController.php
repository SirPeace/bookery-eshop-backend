<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttributeGroup;
use App\Http\Requests\AttributeGroupStoreRequest;

class AttributeGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $attributeGroups = AttributeGroup::all();

        return response()->json([
            "status" => "success",
            "data" => [
                "attribute_groups" => $attributeGroups
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\AttributeGroupStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AttributeGroupStoreRequest $request)
    {
        $this->authorize('create', AttributeGroup::class);

        $attributeGroup = AttributeGroup::create($request->validated());

        return response()->json([
            'status' => 'success',
            'data' => [
                'attribute_group' => $attributeGroup
            ]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AttributeGroup  $group
     * @return \Illuminate\Http\Response
     */
    public function show(AttributeGroup $group)
    {
        return response()->json([
            "status" => "success",
            "data" => [
                "attribute_group" => $group,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AttributeGroup  $attributeGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AttributeGroup $group)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AttributeGroup  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(AttributeGroup $group)
    {
        //
    }
}
