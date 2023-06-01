if (isset($card_number)) {
                
                $loyalty_cards = LoyaltyCard::where('card_number',$card_number)
                            ->where('status',0)
                            ->count();
                
                if ($loyalty_cards == 0) {
                    
                    LoyaltyCard::create([
                        'card_number' => $card_number,
                        'customer_id' => $request->customer_id??null,
                        'customer_name' => $request->customer_name??null,
                        'product_id' => 1,
                        'promotion_id' => $request->promotion_id,
                        'count' => 1,
                        'status' => 0,
                    ]);

                }else{
                    
                    $loyalty_card = LoyaltyCard::where('card_number',$card_number)
                                ->where('status',0)
                                ->first();

                    // return response()->json($loyalty_card);

                    $count = $loyalty_card->count;
                    $order_qty = $value['order_qty'];
                    if ($count < 7) {

                        $loyalty_card->count += $order_qty;  // add count when count is not meet with 7
                        $loyalty_card->save();

                        
                    }else{
                        if ($request->reward == "accept") {

                            $product = Product::find($loyalty_card->product_id);

                            $sizes = Price::where('size','Large')
                                ->where('product_id',$product->id)
                                ->first();

                            foreach ($sizes->ingredients as $ingredient) {
                                
                                $raw_qty = $ingredient->amount;

                                $raw_material = RawMaterial::find($ingredient->raw_material_id);

                                if ( $raw_qty > $raw_material->instock_qty ) {
                                    return $this->sendError('Stock Error');
                                }else{

                                    $raw_material->instock_qty -= $raw_qty;
                                    $raw_material->save();
                                }

                            }

                            $loyalty_card->count += 1;
                            $loyalty_card->status = 1;
                            $loyalty_card->save();

                        }elseif($request->reward == "add"){
                            LoyaltyCard::create([
                                'card_number' => $card_number,
                                'customer_id' => $request->customer_id??null,
                                'customer_name' => $request->customer_name??null,
                                'product_id' => 1,
                                'promotion_id' => $request->promotion_id,
                                'count' => 1,
                                'status' => 0,
                            ]);
                        }
                    }
                }

            }