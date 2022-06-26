<?php

define('DB_SERVER','localhost');
define('DB_USER','id19172239_root');
define('DB_PASS' ,'&0aXhAu1G-LU?eF>');
define('DB_NAME', 'id19172239_dare2complete');
$con = mysqli_connect(DB_SERVER,DB_USER,DB_PASS,DB_NAME);
// Check connection
if (mysqli_connect_errno())
{
 echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
//db connections

session_start();
//static 
$booked=0;
$num_of_seats='';
$total_seats=80;
$num_of_seats_in_row=7;
$input_limit=7;
$message='';
$query = mysqli_query($con, "SELECT * FROM reservation");
$query_rows = mysqli_num_rows($query);

//static


//for seats book @start
if(isset($_REQUEST['book'])){
	$num_of_seats =$_REQUEST['num_of_seats'];

	//checking input limit @start
	if($num_of_seats >0 && $num_of_seats <=$input_limit){

		//filled seats data @start
		$query = mysqli_query($con, "SELECT * FROM reservation");
		$query_res = mysqli_fetch_all($query);
		$booked_seats=[];
		foreach ($query_res as $key => $value) {
			foreach (json_decode($value[1]) as $k => $v) {
				array_push($booked_seats, $v);
			}
			
		}
		//filled seats data @start
		
		//filling every row has 7 seats except last as total seats is 80 @start
		$start =1;
		while(0<$total_seats){
			if($total_seats<$num_of_seats_in_row){
				$total_rows_boking_count[$start]=$total_seats;
				$total_rows_boking_seats[$start]=[];
			}else{
				$total_rows_boking_count[$start]=$num_of_seats_in_row;
				$total_rows_boking_seats[$start]=[];	
			}
			
			$total_seats=$total_seats-$num_of_seats_in_row;
			$start+=1;
		}
		$total_seats=80;
		//filling every row has 7 seats except last as total seats is 80 @end
		//checking remaining seats in the each row @start
			// print_r($booked_seats);die;
		foreach($booked_seats as $key=>$value){
			$split =explode('_',$value);
			$total_rows_boking_count[$split[0]] =$total_rows_boking_count[$split[0]]-1;
			array_push($total_rows_boking_seats[$split[0]],$value);
		}
		//checking remaining seats in the each row @end

		// 4>=5
		if(array_sum($total_rows_boking_count) >=$num_of_seats ){
			$error=0;
			//function to book seat @start
			function book_rows(&$n,$seat_ids,$row_id,&$new_seats){
				if($row_id!=''){
					for ($i=1; $i <=7 ; $i++) { 
						if(0<$n){
							if(!in_array($row_id.'_'.$i, $seat_ids[$row_id])){
								array_push($new_seats,$row_id.'_'.$i);
								$n-=1;
							}
						}
					}
				}
				else{
					foreach ($seat_ids as $key => $value) {
						book_rows($n,$seat_ids,$key,$new_seats);
					}
				}
				
			}
			//filling new seats @end

			//checking whether row seats are available for number of input seats 
			$book_row_id='';
			foreach($total_rows_boking_count as $key=>$value){
				if($num_of_seats<=$value){
					// book_rows($num_of_seats,$total_rows_boking_seats[$key],$key,$new_seats);
					$book_row_id=$key;
					break;
				}
			}
			$new_seats	=[];
			book_rows($num_of_seats,$total_rows_boking_seats,$book_row_id,$new_seats);
			$query = mysqli_query($con, "INSERT INTO `reservation` (`id`, `seats`, `seat_count`) VALUES 
				(NULL, '".json_encode($new_seats)."','".count($new_seats)."');");
			$query_rows=$query;
		}else{
			if(array_sum($total_rows_boking_count) ==0){
				$msg='Seats are fulled';	
			}else{
				$msg='Only '.array_sum($total_rows_boking_count).' seats are available';
			}
			
			$error=1;
			$new_seats=[];
		}
		$booked =1;
	
	}else{
		$message="<script>alert('Number of seats should be 1 to 7')</script>";
	}
	//checking input limit @end

		
	
}
//for seats book @end

//for deleting data @start
if(isset($_REQUEST['delete'])){
$query = mysqli_query($con, "DELETE FROM `reservation`");
$message="<script>alert('Deleted data successfully')</script> ";
$query_rows=0;
}
//for deleting data @end

//for inserting some sample data when we dont have any data @start
if(isset($_REQUEST['sample_data'])){
$sample_data =json_encode(['0'=>'1_1','1'=>'1_2']);
$query = mysqli_query($con, "INSERT INTO `reservation` (`id`, `seats`, `seat_count`) VALUES 
				(NULL, '".json_encode($new_seats)."','2')");
$message="<script>alert('Deleted data successfully')</script> ";
$query_rows=1;
}
//for inserting some sample data when we dont have any data @end

?>
<!DOCTYPE html>
<html>
<head>
	<title>Ticket Booking</title>
	<link rel = "stylesheet" 
         href = "https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" 
         integrity = "sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" 
         crossorigin = "anonymous">
	
	<style type="text/css">
		.checkbox_cla{
			opacity: 0
		}
		.checkboxes ul, .checkboxes li {
		    margin:0;
		    padding:0;
		    /*list-style:none;*/
		}
		.checkboxes li {
		    display:inline-block;
		}
		.box{
			width: 25%;
		    border: 2px solid red;
		    border-radius: 50px;
		}
		.past_seat{
			background-color: yellow;
		    opacity: 1;
		    border-radius: 50%;
		}
		.present_seat{
			background-color: green;
		    opacity: 1;
		    border-radius: 50%;
		}
		html, body {
		    height: 100%;
		}

	</style>
</head>

<body>
	<?php echo $message;?>
	<div class = "container">
         <center> <h2>Ticket Reservation</h2></center>
         <form action="" method="POST">
            <div class = "form-group">
               <label for = "bookseat">Enter number of Seats</label>
               <input type = "number" class = "form-control" 
                  id = "bookseat" name="num_of_seats">
            </div>
            
            <input type="submit" name="book"  class="btn btn-primary" value="Book">
            <input type="submit" name="delete"  class="btn btn-success" value="Delete">
            <?php if ($query_rows ==0) { ?>
            	<input type="submit" name="sample_data"  class="btn btn-success" value="Sample Data">
            <?php } ?>
            <!-- <button type = "submit" class = "btn btn-primary" value="book">Book</button> -->
         </form>
	</div>
	<?php  if($booked==1) { ?>
	<div class = "container" >
      	<div style="width:15%;float: left;margin: 10px 10px">
      		<ul class="checkboxes">
      			<li><label class="past_seat"><input type="checkbox"  style="opacity: 0" /></label>Booked
      			</li>
      			<?php if(!empty($new_seats)) { ?>
	      			<li><label class="present_seat"><input type="checkbox"  style="opacity: 0" /></label>Your seats
	      			</li>
      			<?php } ?>
      		</ul>
      		<?php if(!empty($new_seats)) { ?>
	      		<h5>Your Seats</h5>
	      		<?php echo implode(',', $new_seats) ;?>
      		<?php } ?>
      		
      	</div>
      	
      	<div style="width:80%;float: right;margin: 10px 10px">
			<center> <h3><?php if($error ==1){echo $msg;}?></h3></center>
				<?php 
				$count =0;
				$k=0;
				$j=1;
				for($i=1;$i<$total_seats+1;$i++) {
					if ($count==0){
						$k+=1;
						echo '<ul class="checkboxes">';
						echo '<li><label>'.$k.'--></label></li>';
					}
					?>
						<?php $set_value = $k.'_'.$j;
						$seat_class='';
						if (in_array($set_value, $new_seats)){
							$seat_class='present_seat';
						}elseif (in_array($set_value, $booked_seats)) {
							$seat_class='past_seat';
						}
						?>
					    <li><label class="<?php echo $seat_class;?>"><input type="checkbox" class="checkbox_cla" value="<?php echo $set_value ?>"/><?php echo $j;?></label></li>

					<?php 
						$count ++;
						$j++;
						if ($count==$num_of_seats_in_row){
							$count=0;
							$j=1;
							echo '</ul>';
						}
				} ?>
      	</div>
    </div>
    <?php } ?>
	
</body>
</html>