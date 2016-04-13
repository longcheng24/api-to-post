<?php
/*
 Author: Long Cheng
 This template is for saving API data to customized post type including feathered image, customized fields
 Data from bigOven API
*/
?>


<?php
function __update_post_meta( $post_id, $field_name, $value = '' )
										{
											if ( empty( $value ) OR ! $value )
											{
												delete_post_meta( $post_id, $field_name );
											}
											elseif ( ! get_post_meta( $post_id, $field_name ) )
											{
												add_post_meta( $post_id, $field_name, $value );
											}
											else
											{
												update_post_meta( $post_id, $field_name, $value );
											}
										}
									$getdata = http_build_query($_GET); 
									 $opts = array('http' => 
										array( 
											'method'  => 'GET', 
											'header'  => 'Accept: application/json', 											
											'content' => $getdata 
										) 
									); 
									$context  = stream_context_create($opts); 
									$recipeArray = array(383432,175103,719913,234958,602212,1191578,368365,1109520,186165,182129,341329,181527,1781,173391,165449,159796,161961,163933,171664,160170,285241,138181,471200,127667,163483,158412,119803,466985,166963,890845,184208,258588,588903,476686,741441,207067,162285,706098,200674,160081,379111,1231513,522589,1243475,175669,166256,176947,167421,157915);
									$recipeArray1 = array(1197765,172967,175935,183592,170282,1163224,178675,159347,117611,561988,1175861,585891,162426,658722,1186027,301540,1178846,384313,1209495,219711,1286626,1275252,229084,115622,209110,1258637,1145184,159485,14205,503753,720329,479404,1181997,162984,171317,171068,307992,883971);
									for($i=0;$i<50;$i++){
									$url = "http://api.bigoven.com/recipe/".$recipeArray1[$i]."?api_key=4rHgn71zbAj4mBD09eW94Ro0N88G76oA";
    								$result = file_get_contents($url, false, $context); 
									$parsed_json = json_decode($result);
									if($recipeImg = $parsed_json -> ImageURL){
										$recipeTitle = $parsed_json -> Title;
										$recipeCategory = $parsed_json -> Category?$parsed_json -> Category:"Other";
										$recipeImg = $parsed_json -> ImageURL;
										$recipeIngredientArray = $parsed_json -> Ingredients;
										$recipeIngredient = NULL;
										foreach($recipeIngredientArray as $singleIngredient){
											$ingredientAmount = round($singleIngredient -> Quantity,2);
											$ingredientUnit = $singleIngredient -> Unit;
											$ingredientName = $singleIngredient -> Name;
											$recipeIngredient .= $ingredientAmount." ".$ingredientUnit." ".$ingredientName."<br />";								
										}	
										$recipeDescription = $parsed_json -> Description?$parsed_json -> Description:"Are you out of recipe ideas for tonight? Try this great recipe and stock up at your local Ultra Foods store!";
										$recipeInstructions = 	$parsed_json -> Instructions;
										$recipeTotaltime = 			$parsed_json -> TotalMinutes?$parsed_json -> TotalMinutes:30;
										$recipeActivetime = 		$parsed_json -> ActiveMinutes?$parsed_json -> ActiveMinutes:20;
										$recipePretime = $recipeTotaltime-$recipeActivetime;
										$recipeServing = $parsed_json -> YieldNumber;
										
										$category_id = get_cat_ID($recipeCategory);
										if($category_id == 0) {
											$cat_name = array('cat_name' => $recipeCategory);
											$my_cat_id = wp_insert_term($recipeCategory,'category');
										}
										$new_category_id = get_cat_ID($recipeCategory);
										$my_post = array(
											 'post_title' => $recipeTitle,
											 'post_type' => 'recipes',
											 'post_content' => $recipeDescription,
											 'post_status' => 'publish',
											 'post_author' => 1,
											 'post_category' => array($new_category_id)
										  );
										
										// Insert the post into the database
										  $the_post_id = wp_insert_post( $my_post );
										 
										  
										  __update_post_meta( $the_post_id, 'preparation_time', $recipePretime.' MINS' );
										  __update_post_meta( $the_post_id, 'cooking_time', $recipeActivetime.' MINS' );
										  __update_post_meta( $the_post_id, 'servings', $recipeServing );
										  __update_post_meta( $the_post_id, 'recipe_image', $recipeImg );
										  __update_post_meta( $the_post_id, 'ingredients', $recipeIngredient);
										  __update_post_meta( $the_post_id, 'preparation_instructions', $recipeInstructions);
										  
										  $upload_dir = wp_upload_dir();
										  $image_data = file_get_contents($recipeImg);
										  $filename = basename($recipeImg);
										  if(wp_mkdir_p($upload_dir['path']))
												$file = $upload_dir['path'] . '/' . $filename;
										  else
												$file = $upload_dir['basedir'] . '/' . $filename;
										  file_put_contents($file, $image_data);
											
										  $wp_filetype = wp_check_filetype($filename, null );
											$attachment = array(
												'post_mime_type' => $wp_filetype['type'],
												'post_title' => sanitize_file_name($filename),
												'post_content' => '',
												'post_status' => 'inherit'
											);
											$attach_id = wp_insert_attachment( $attachment, $file, $the_post_id );
											require_once(ABSPATH . 'wp-admin/includes/image.php');
											$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
											wp_update_attachment_metadata( $attach_id, $attach_data );
											
											set_post_thumbnail( $the_post_id, $attach_id );
																					 
									}
									}
																	
?>