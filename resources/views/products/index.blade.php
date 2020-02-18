@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h1>Products</h1>
                <hr>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Title</th>
                            <th scope="col">Price</th>
                            <th scope="col">Image</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($products as $product)
                            <tr class="accordion-toggle @if(isset($product->variants[1])) collapsed @endif"
                                id="accordion{{$product->id}}" data-toggle="collapse"
                                data-parent="#accordion1" href="#collapse{{$product->id}}">
                                <td class="expand-button"></td>
                                <td>{{$product->title}}</td>
                                <td>{{$product->variants[0]->price}}</td>
                                <td>@if($product->images[0]->src)
                                        <div class="thumbnail">
                                            <img height="60" width="60" src="{{ $product->images[0]->src }}"
                                                 alt="{{ $product->title }}">
                                        </div>
                                        @else
                                        &mdash;
                                    @endif</td>
                            </tr>
                            @if(isset($product->variants[1]))
                                @foreach($product->variants as $k => $sub_p)
                                    @if($k>0)
                                        <tr class="hide-table-padding">
                                            <td></td>
                                            <td class="sub-breed-td" colspan="3">
                                                <div id="collapse{{$product->id}}" class="collapse in p-3">
                                                    <div class="row">
                                                        <div class="col-2 sub_breed">{{$sub_p->title}}</div>
                                                        <div class="col-6 sub_breed">{{$sub_p->price}}</div>
                                                        <div class="col-6 sub_breed">

                                                            @if(isset($product->images[$k]->src))
                                                                <div class="thumbnail">
                                                                    <img height="60" width="60"
                                                                         src="{{ $product->images[$k]->src }}"
                                                                         alt="{{ $sub_p->title }}">
                                                                </div>
                                                                @else
                                                                &mdash;
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
