<html>
    <body>
        <?php
        // Turning Off Error Reporting...
        error_reporting(E_ALL & E_NOTICE);//should be ~E_NOTICE instead of E_NOTICE :(
        if(isset($_POST['item_s'])){     
            /*
                first, coverts string to lower case
                then, replace space with +
                finally, store into $text
            */
            $text = str_replace(" ","+",strtolower($_POST['item_s']));

            //Get web page data from url
            $web_page_data = file_get_contents("http://www.pricetree.com/search.aspx?q=".$text);

            //we need particular data from page not an entire web page i.e echo $web_page_data;
            //from entire web page it will split contents based on "items-wrap"
            $item_list = explode('<div class="items-wrap"',$web_page_data); 

            //$item_list is an array so use print_r
            //print_r($item_list);

            $i=1;
            if(sizeof($item_list)<2){
                echo 'No Products Found';
                $i=sizeof($item_list);//edited from 5
            }

            //avoid array[0] and loop for total items-wrap
            for($i;$i<sizeof($item_list);$i++){
                //echo $item_list[$i];//this is array seperated based on split string <div...>
                //We want title and another information
                //it is printing on sizeof($item_list) times
                //this is printing only sizeof($item_list) items from url with words <div...>
                //from list item split based on href=" and then " because we want url b/w them.

                $url_link1 = explode('href="',$item_list[$i]);
                 // $url_link[0] will be before http=" data
                $url_link2 = explode('"',$url_link1[1]);
                //echo $url_link2[0]."</BR>"; //Split by " and before that

                //now image link same as original but split with data-original="

                $image_link1 = explode('data-original="',$item_list[$i]);
                // $image_link[0] will be before data-otiginal=" data
                $image_link2 = explode('"',$image_link1[1]); 
                //echo $image_link2[0]."</BR>"; //Split by " and before that

                //We want title and only available
                //Getting title split b/w title=" and "
                $title1 = explode('title="',$item_list[$i]);
                $title2 = explode('"',$title1[1]);

                //get only aviailable items
                //split b/w avail-stores"> and </div>
                $available1 = explode('avail-stores">',$item_list[$i]);
                $available = explode('</div>',$available1[1]);
                if( strcmp($available[0],"Not available") == 0 ){
                    //if content is not avialable
                    continue;
                    //goto next item in array and continue loop
                }


                $item_title = $title2[0];
                //if $item_title has no data then continue to loop
                if(strlen($item_title)<2){
                    continue;
                }
                //storing values into variables for better productivity
                $item_link = $url_link2[0];
                $item_image_link = $image_link2[0];
                $item_id1 = explode("-",$item_link);
                $item_id = end($item_id1);//split with "." and print last one after split i.e. id



                //goto pricetree access api to get price list
                //price list will be accessible based on $item_id

                $request = "http://www.pricetree.com/dev/api.ashx?pricetreeId=".$item_id."&apikey=7770AD31-382F-4D32-8C36-3743C0271699";
                $response = file_get_contents($request);
                $results = json_decode($response, TRUE);
                //show data fetched into the browser using html tags
                echo '<img src="'.$item_image_link.'" style="width:150px;height:200px;float:left;">';
                echo '
                    <table border=5px solid style="min-height:200px;width:50%;">
                        <caption>'.$item_title.'</caption>
                        <tr>
                            <td>From</td>
                            <td>Price</td>
                            <td>Get It</td>
                        </tr>
                        ';
                //Fetch Sprcific Data items as required to provide
                foreach($results['data'] as $item_data){
                    $seller = $item_data['Seller_Name'];
                    $price = $item_data['Best_Price'];
                    $product_link = $item_data['Uri'];

                    //fill out these data into table
                    echo '
                        <tr>            
                            <td>'.$seller.'</td>
                            <td>'.$price.'</td>
                            <td><a href="'.$product_link.'">Buy Now</a></td>
                        </tr>';
                }
                echo '
                    </table>
                '.'</BR>';

            }

        }
        else{
            echo 'sorry...';
        }
        ?>
        
    </body>
</html>