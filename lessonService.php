<?php 
require 'includes/database.php';
require 'includes/validate.php';

session_start();


switch($_POST["functionname"])
{
    case 'BookLesson':
        BookLesson();
        break;
    case 'Cancel':
        Cancel();
        break;
    case 'Update':
        Update();
        break;
    case 'AttendLesson':
        AttendLesson();
        break;   
    case 'SubmitFeedback':
        SubmitFeedback();
        break;   
    case 'SignUp':
        SignUp();
        break;
    case 'CheckIfBookingAllowed':
        CheckIfBookingAllowed();
        break;
}

function BookLesson(){

$conn=getDB();

$lessonId=$_POST['lessonId'];
$cutomerId=$_POST['customerId'];

echo $lessonId;
echo $cutomerId;

$check="SELECT * FROM `booked_lesson` WHERE FL_TT_ID=$lessonId AND C_ID=$cutomerId";

$result=$conn->query($check);

$data=$result->fetch_assoc();
if($data == null){
    $insertSQL = "INSERT INTO booked_lesson(FL_TT_ID, C_ID, Status) VALUES ('$lessonId', '$cutomerId', 'Booked')";
    $insertResult=$conn->query($insertSQL);
    if($insertResult===true){
        echo "Lesson booked successfully";
    }else{
        echo "Failed to save record";
    }
    exit();
}
else if($data["Status"]=="Booked"){
    echo "Booking already exist. please try another record.";
    exit();
  }
else{
    $sql="UPDATE `booked_lesson` SET Status ='Booked' WHERE FL_TT_ID=$lessonId AND C_ID=$cutomerId"  ;
    $result=$conn->query($sql);
    if($result===true){
        echo "Lesson booked successfully";
    }else{
        echo "Failed to save record";
    }
}
$conn->close();
}

function Cancel(){
    $conn=getDB();

    $lessonId=$_POST['lessonId'];
    $cutomerId=$_POST['customerId'];

    $check="SELECT * FROM `booked_lesson` WHERE FL_TT_ID=$lessonId AND C_ID=$cutomerId AND Status='Cancelled'";
    
    $result=$conn->query($check);

    
if($result->num_rows > 0){
    echo "Lesson alraedy cancelled. please try another record.";
    exit();
  }else{
    $sql="UPDATE booked_lesson SET STATUS='Cancelled' WHERE FL_TT_ID=$lessonId AND C_ID=$cutomerId";
    $result=$conn->query($sql);
    if($result===true){
        echo "Lesson cancelled successfully";
    }else{  
        echo "Failed to cancel record";
    }
  }
  $conn->close();
}

function Update(){
    $conn=getDB();

    $lessonId=$_POST['lessonId'];
    $bookingId=$_POST['bookingId'];

    $check="SELECT * FROM booked_lesson WHERE STATUS='Cancel' ||  STATUS='Changed'  AND Id=$bookingId";
    
    $result=$conn->query($check);
    
    if($result->num_rows > 0){
        echo "Lesson is already cancelled or changed. please try another record.";
        exit();
      }
    else{
        $sql="UPDATE booked_lesson SET FL_TT_ID=$lessonId, STATUS='Changed' WHERE Id=$bookingId";
        $result=$conn->query($sql);
        if($result===true){
            echo "Lesson changed successfully.";
        }else{
            echo "Failed to change record";
        }
    }
}

function AttendLesson(){
    $conn=getDB();
    $bookingId=$_POST['bookingId'];

    $check="SELECT Status FROM booked_lesson WHERE Id=$bookingId LIMIT 1 ";

    $result=$conn->query($check);
    $data=$result->fetch_assoc();

    if($data["Status"]=="Cancelled" ||$data["Status"]=="Attended"){
        echo "Lesson is already cancelled or changed or attened. please try another record.";
        exit();
      }
    else{

        $sql="UPDATE booked_lesson SET STATUS='Attended' WHERE Id=$bookingId";
        $result=$conn->query($sql);
        if($result===true){
            echo "Lesson attened successfully.";
        }else{
            echo "Failed to attend lesson.";
        }
    }

    }


    function SubmitFeedback(){
        $conn=getDB();
        $comments=$_POST['comments'];
        $rating=$_POST['rating'];
        $bookingId=$_POST['bookingId'];
    
        $sql="INSERT INTO customer_review(R_ID, B_ID, Comment) VALUES ('$rating' , '$bookingId' , '$comments')";
    
        $result=$conn->query($sql);
        if($result===true){
            echo "Review saved successfully";
        }else{
            echo "Failed to save review";
        }
    }

    function SignUp(){
        $conn=getDB();
        
        
        $fname=$_POST['fname'];
        $address=$_POST['address'];
        $email=$_POST['email'];
        $password=$_POST['password'];
        $confirmPass=$_POST['confirmPassword'];
        if(validateEmail($email)==false){
            echo "invalid email";
        }
        elseif ($fname=='' || $address=='' || $email=='' || $password=='' || $confirmPass=='') {
            echo 'One or more fields are empty';
        }
        elseif(!empty(getUser($conn, $_POST['email']))){
            echo "already user exist";
        }
        elseif($password==$confirmPass){
                $sql="INSERT INTO customer (Username, Address, FullName, Password) VALUES(?,?,?,?)";//?is a placeholder for record item
                $stmt=mysqli_prepare($conn,$sql);
                if($stmt===false){
                    echo mysqli_error($conn);
                }else{
                    mysqli_stmt_bind_param($stmt,"ssss",$email,$address,$fname,$password);
                    // here ss is to pass string values and i is to pass integer values
                    if(mysqli_stmt_execute($stmt)){
                        echo "Sign Up is successful";
                    }    
                    else{
                        echo mysqli_stmt_error($stmt);
                    }
                }
            }else{
                echo 'password dont match';
            }
           
    }

    function CheckIfBookingAllowed(){
       

        $timeTableId=$_POST['timeTableId'];
        if($timeTableId == null){
            echo "false";
            return;
        }
        
        $conn=getDB();

        $lessonSQL = "SELECT * FROM fitnesslesson_timetable WHERE Id=$timeTableId";
        $result=$conn->query($lessonSQL);
        $data=$result->fetch_assoc();
        if($data != null){
            $lessonId = $data['FL_ID'];
            $sql = "SELECT * FROM `booked_lesson` bl INNER JOIN fitnesslesson_timetable ft on bl.FL_TT_ID = ft.Id
            WHERE ft.FL_ID=${lessonId} AND bl.Status='Booked'";
            $result=$conn->query($sql);
            if($result->num_rows > 5){
                echo "false";
                $conn->close();
                return;
            }
        }
       
        echo "true";
        $conn->close();
        return;
    }

?>