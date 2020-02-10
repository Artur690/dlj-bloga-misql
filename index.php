<?php
$time=time();
if (session_id()=='') session_start();

$db=mysqli_connect("localhost","root","","test") or die();
$res=mysqli_query($db,"set test utf8");

$mess_url=mysqli_real_escape_string($db,basename($_SERVER['SCRIPT_FILENAME']));

//получаем id текущей темы
$res=mysqli_query($db,"SELECT id FROM таблица WHERE file_name='".$mess_url."'");
$res=mysqli_fetch_array($res);
$theme_id=$res["id"];

if (isset($_POST["contr_cod"])){    //отправлен комментарий
 $mess_login=htmlspecialchars($_POST["mess_login"]);
 $user_text=htmlspecialchars($_POST["user_text"]);
 if (md5($_POST["contr_cod"])==$_POST["prov_summa"]){    //код правильный
  if ($mess_login!='' and $user_text!=''){
   if (is_numeric($_POST["parent_id"]) and is_numeric($_POST["f_parent"]))
    $res=mysqli_query($db,"insert into comment
    (parent_id, first_parent, date, theme_id, login, message)
    values ('".$_POST["parent_id"]."','".$_POST["f_parent"]."',
    '".$time."','".$theme_id."','".$mess_login."','".$user_text."')");
   else $res=mysqli_query($db,"insert into comment (date, theme_id, login, message)
   values ('".$time."','".$theme_id."','".$mess_login."','".$user_text."')");
    $_SESSION["send"]="Комментарий принят!";
    header("Location: $mess_url#last"); exit;
  }
  else {
   $_SESSION["send"]="Не все поля заполнены!";
   header("Location: $mess_url#last"); exit;
  }
 }
 else {
  $_SESSION["send"]="Неверный проверочный код!";
  header("Location: $mess_url#last"); exit;
 }
}

if (isset($_SESSION["send"]) and $_SESSION["send"]!="") {    //вывод сообщения
    echo '<script type="text/javascript">alert("'.$_SESSION["send"].'");</script>';
    $_SESSION["send"]="";
}
?>
<?php
function parents($up=0, $left=0) {
global $tag,$mess_url;
for ($i=0; $i<=count($tag[$up])-1; $i++) {
if ($tag[$up][$i][2]=='Admin') $tag[$up][$i][2]='<font color="#C00">Admin</font>';
if ($tag[$up][$i][6]==0) $tag[$up][$i][6]=$tag[$up][$i][0];
$sum=$tag[$up][$i][4]-$tag[$up][$i][5];
if ($up==0) echo '<div style="padding:5px 0 0 0;">';
else {
if (count($tag[$up])-1!=$i)
echo '<div class="strelka" style="padding:5px 0 0 '.($left-2).'px;">';
else echo '<div class="strelka_2" style="padding:5px 0 0 '.$left.'px;">';
 }
 echo '<div class="comm_head" id="m'.$tag[$up][$i][0].'">';
 echo '<div style="float:left;"><b>'.$tag[$up][$i][2].'</b></div>';
 echo '<div class="comm_minus"></div>';
 echo '<div style="float:right; width:30px;" id="rating_comm'.$tag[$up][$i][0].'">';
 echo '<b>'.$sum.'</b></div><div class="comm_plus"></div>';
 echo '<a style="float:right; width:70px;" href="'.$mess_url.'#m';
 echo $tag[$up][$i][0].'"># '.$tag[$up][$i][0].'</a>';
 echo '<div style="float:right; width:170px;">';
 echo '('.date("H:i:s d.m.Y", $tag[$up][$i][3]).' г.)</div>';
 echo '<div style="clear:both;"></div></div>';
 echo '<div class="comm_body">';
 if ($sum<0) echo '<u class="sp_link">Показать/скрыть</u><div class="comm_text">';
 else echo '<div style="word-wrap:break-word;">';
 echo str_replace("<br />","<br>",nl2br($tag[$up][$i][1])).'</div>';
 echo '<div class="open_hint" onClick="comm_on('.$tag[$up][$i][0].',
     '.$tag[$up][$i][6].')">Ответить</div><div style="clear:both;"></div></div>';

 if (isset($tag[ $tag[$up][$i][0] ])) parents($tag[$up][$i][0],20);
 echo '</div>';
}
}
$res=mysqli_query($db,"SELECT * FROM comment
    WHERE theme_id='".$theme_id."' and moderation=1 ORDER BY id");
$number=mysqli_num_rows($res);

if ($number>0) {
 echo '<div style="border:1px solid #000000;padding:5px;text-align:center;">';
 echo '<b>Последние комментарии:</b><br>';
 while ($com=mysqli_fetch_assoc($res))
    $tag[(int)$com["parent_id"]][] = array((int)$com["id"], $com["message"],
    $com["login"], $com["date"], $com["plus"], $com["minus"], $com["first_parent"]);
 echo parents().'</div><br>';
}
?>
<?php
$cod=rand(10,90); $cod2=rand(1,99);
echo '<div id="last" align="center">';
echo '<form method="POST" action="'.$mess_url.'#last" class="add_comment"';
echo 'name="add_comment" id="hint"><div class="close_hint">Закрыть</div>';
echo '<textarea cols="68" rows="5" name="user_text"></textarea>';
echo '<div style="margin:5px; float:left;">';
echo 'Имя: <input type="text" name="mess_login" maxlength="20" value=""></div>';
echo '<div style="margin:5px; float:right;">'.$cod.' + '.$cod2.' = ';
echo '<input type="hidden" name="prov_summa" value="'.md5($cod+$cod2).'">';
echo '<input type="hidden" name="parent_id" value="0">';
echo '<input type="hidden" name="f_parent" value="0">';
echo '<input type="text" name="contr_cod" maxlength="4" size="4">&nbsp;';
echo '<input type="submit" value="Отправить"></div>';
echo '</form>';
echo '<form method="POST" action="'.$mess_url.'#last" class="add_comment">';
echo 'Добавить комментарий:';
echo '<textarea cols="68" rows="5" name="user_text"></textarea>';
echo '<div style="margin:5px; float:left;">';
echo 'Имя: <input type="text" name="mess_login" maxlength="20" value=""></div>';
echo '<div style="margin:5px; float:right;">'.$cod.' + '.$cod2.' = ';
echo '<input type="hidden" name="prov_summa" value="'.md5($cod+$cod2).'">';
echo '<input type="text" name="contr_cod" maxlength="4" size="4">&nbsp;';
echo '<input type="submit" value="Отправить"></div>';
echo '</form></div>';
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js">
</script>
<script type="text/javascript">
//Добавление в форму отправки комментария значений id родительских комментариев
function comm_on(p_id,first_p){
    document.add_comment.parent_id.value=p_id;
    document.add_comment.f_parent.value=first_p;
}

$(document).ready(function(){
//Показать скрытое под спойлером сообщение
$(".sp_link").click(function(){
    $(this).parent().children(".comm_text").toggle("normal");
});
//Показать форму ответа на имеющийся комментарий
$(".open_hint").click(function(){
    $("#hint").animate({
        top: $(this).offset().top + 25, left: $(document).width()/2 -
        $("#hint").width()/2
    }, 400).fadeIn(800);
});

//Скрыть форму ответа на имеющийся комментарий
$(".close_hint").click(function(){ $("#hint").fadeOut(1200); });

//Получение id оцененного комментария
$(".comm_plus,.comm_minus").click(function(){
    id_comm=$(this).parents(".comm_head").attr("id").substr(1);
});

//Отправление оценки комментария в файл rating_comm.php
$(".comm_plus").click(function(){
    jQuery.post("rating_comm.php",{comm_id:id_comm,ocenka:1},rating_comm);
});
$(".comm_minus").click(function(){
    jQuery.post("rating_comm.php",{comm_id:id_comm,ocenka:0},rating_comm);
});

//Возврат рейтинга комментария и его обновление
function rating_comm(data){
    $("#rating_comm"+id_comm).fadeOut(800,function(){
        $(this).html(data).fadeIn(800);
    });
}
});
</script>
<?php
if (isset($_POST["comm_id"]) and is_numeric($_POST["comm_id"]))
    $obj=$_POST["comm_id"];
else $obj='';

if (isset($_POST["ocenka"]) and ($_POST["ocenka"]==0 or $_POST["ocenka"]==1))
    $ocenka=$_POST["ocenka"];
else $ocenka='';

if ($ocenka!='' and $obj>0) {
 $ip=$_SERVER['REMOTE_ADDR'];

 $db=mysqli_connect("localhost","artur008","","test") or die();
 $res=mysqli_query($db,"SELECT count(id),ocenka FROM ocenka_comment
         WHERE comment_id='".$obj."' and ip=INET_ATON('".$ip."')");

 $number=mysqli_fetch_array($res);
 if ($number[0]==0) {
    $res=mysqli_query($db,"INSERT INTO ocenka_comment (date,comment_id,ip,ocenka)
    values ('".time()."','".$obj."',INET_ATON('".$ip."'),'".$ocenka."')");

    if ($ocenka==0) $res=mysqli_query($db,"UPDATE comment SET minus=(minus+1)
                    WHERE id='".$obj."' LIMIT 1");
    else $res=mysqli_query($db,"UPDATE comment SET plus=(plus+1)
                    WHERE id='".$obj."' LIMIT 1");
 }
 elseif ($number["ocenka"]!=$ocenka) {
    $res=mysqli_query($db,"UPDATE ocenka_comment SET date='".time()."',
    ocenka='".$ocenka."' WHERE comment_id='".$obj."' and ip=INET_ATON('".$ip."')");

    if ($ocenka==0) $res=mysqli_query($db,"UPDATE comment SET minus=(minus+1),
                    plus=(plus-1) WHERE id='".$obj."' LIMIT 1");
    else $res=mysqli_query($db,"UPDATE comment SET plus=(plus+1), minus=(minus-1)
                    WHERE id='".$obj."' LIMIT 1");
 }

$res=mysqli_query($db,"SELECT plus,minus FROM comment WHERE id='".$obj."' LIMIT 1");
$rating=mysqli_fetch_array($res);
echo '<b>'.( $rating["plus"]-$rating["minus"]).'</b>';
mysqli_close($db);
}
?>

<?php
$del_date=$time-2592000;    //время в секундах (2592000 сек. = 30 дней)
$res=mysqli_query($db,"DELETE FROM ocenka_comment WHERE date<".$del_date."");
?>