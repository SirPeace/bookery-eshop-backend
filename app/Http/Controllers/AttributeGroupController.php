<?php

namespace App\Http\Controllers;

use App\Models\AttributeGroup;
use Illuminate\Http\Request;

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
