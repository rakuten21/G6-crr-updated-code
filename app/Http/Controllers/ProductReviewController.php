<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Notification;
use App\Notifications\StatusNotification;
use App\Models\User;
use App\Models\ProductReview;
use App\Models\Order;
class ProductReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reviews=ProductReview::getAllReview();
        
        return view('backend.review.index')->with('reviews',$reviews);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'rate'=>'required|numeric|min:1'
        ]);
        $product_info=Product::getProductBySlug($request->slug);
        $order = auth()->user()->orders()->latest()->first();

    if (!$order) {
        return redirect()->back()->with('error', 'No orders found for the current user.');
    }

    // Check if the order contains the product being reviewed
    $orderItems = $order->cart_info()->where('product_id', $product_info->id)->get();
    if ($orderItems->isEmpty()) {
        return redirect()->back()->with('error', 'You can only review products that you have purchased.');
    }

    // Check if the order status is 'delivered' before allowing review submission
    if ($order->status !== 'delivered') {
        return redirect()->back()->with('error', 'You can only review products from delivered orders.');
    }

    // Check if there is already a review for this order
    $existingReview = ProductReview::where('order_id', $order->id)
        ->where('product_id', $product_info->id)
        ->first();

    if ($existingReview) {
        // If a review already exists, prevent the user from submitting another review
        return redirect()->back()->with('error', 'You have already submitted a review for this product.');
    }
        //  return $product_info;
        // return $request->all();
        $data = $request->all();
        $data['product_id'] = $product_info->id;
        $data['user_id'] = $request->user()->id;
        $data['order_id'] = $order->id;
        $data['status'] = 'active';
        // dd($data);
        $status=ProductReview::create($data);

        $user=User::where('role','admin')->get();
        $details=[
            'title'=>'New Product Rating!',
            'actionURL'=>route('product-detail',$product_info->slug),
            'fas'=>'fa-star'
        ];
        Notification::send($user,new StatusNotification($details));
        if($status){
            request()->session()->flash('success','Thank you for your feedback');
        }
        else{
            request()->session()->flash('error','Something went wrong! Please try again!!');
        }
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $review=ProductReview::find($id);
        // return $review;
        return view('backend.review.edit')->with('review',$review);
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
        $review=ProductReview::find($id);
        if($review){
            // $product_info=Product::getProductBySlug($request->slug);
            //  return $product_info;
            // return $request->all();
            $data=$request->all();
            $status=$review->fill($data)->update();

            // $user=User::where('role','admin')->get();
            // return $user;
            // $details=[
            //     'title'=>'Update Product Rating!',
            //     'actionURL'=>route('product-detail',$product_info->id),
            //     'fas'=>'fa-star'
            // ];
            // Notification::send($user,new StatusNotification($details));
            if($status){
                request()->session()->flash('success','Review Successfully updated');
            }
            else{
                request()->session()->flash('error','Something went wrong! Please try again!!');
            }
        }
        else{
            request()->session()->flash('error','Review not found!!');
        }

        return redirect()->route('review.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $review=ProductReview::find($id);
        $status=$review->delete();
        if($status){
            request()->session()->flash('success','Successfully deleted review');
        }
        else{
            request()->session()->flash('error','Something went wrong! Try again');
        }
        return redirect()->route('review.index');
    }
}
