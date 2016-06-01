<?php
  /**
   * This file is modified
   * by yybird
   * @2016.05.25
  **/
?>

<?php 
  require("admin-header.php");
  require_once("permission.php");
  $type = "OJ";
  if (isset($_GET['type'])) {
    $type = $_GET['type'];
  }
  if(isset($OJ_LANG)){
    require_once("../lang/$OJ_LANG.php");
  }
  require_once("../include/set_get_key.php");
  if ($type=="C" && !$GE_TA) {
    echo "Permission denied!";
    exit(1);
  }
  if ($type=="OJ" && !$GE_T) {
    echo "Permission denied!";
    exit(1);
  }
  $keyword=$_GET['keyword'];
  $keyword=mysql_real_escape_string($keyword);
  if ($type == "OJ")
    $sql="SELECT max(`problem_id`) as upid FROM `problem` WHERE problem_id<'$BORDER'";
  else 
    $sql="SELECT max(`problem_id`) as upid FROM `problem` WHERE problem_id>='$BORDER'";
  $page_cnt=100;
  $result=mysql_query($sql);
  echo mysql_error();
  $row=mysql_fetch_object($result);
  if ($type == "OJ")
    $cnt = intval($row->upid)-1000;
  else 
    $cnt = intval($row->upid)-$BORDER;
  $cnt = intval($cnt/$page_cnt)+(($cnt%$page_cnt)>0?1:0); // cnt是页数
  if (isset($_GET['page'])) $page=intval($_GET['page']);
  else $page = $cnt;
  if ($type == "OJ") {
    $pstart = 1000+$page_cnt*intval($page-1);
  } else {
    $pstart = $BORDER+$page_cnt*intval($page-1);
  }
  $pend = $pstart+$page_cnt;
?>
  <title>Problem List</title>
  <center><h2>Problem List</h2></center>

  <form action=problem_list.php>
    <select class='input-mini' onchange="location.href='problem_list.php?type=<?php echo $type?>&page='+this.value;" style='width:80px'>
    <?php
      for ($i=1;$i<=$cnt;$i++){
        if ($i>1) echo '&nbsp;';
        if ($i==$page) echo "<option value='$i' selected>";
        else  echo "<option value='$i'>";
        if ($type == "OJ") echo $i+9;
        else echo 5000+($i-1);
        echo "**</option>";
      }
    ?>
    </select>
<?php
  $sql="select `problem_id`,`title`,`in_date`,`defunct` FROM `problem` where problem_id>=$pstart and problem_id<$pend order by `problem_id` desc";
  //echo $sql;
  if($keyword) $sql="select `problem_id`,`title`,`in_date`,`defunct` FROM `problem` where title like '%$keyword%' or source like '%$keyword%'";
  $result=mysql_query($sql) or die(mysql_error());
  ?>
  <form action=problem_list.php><input name=keyword><input type=submit value="<?php echo $MSG_SEARCH?>" ></form>

  <?php
  echo "<center><table class='table table-striped' width=90% BORDER=1>";
  echo "<form method=post action=contest_add.php>";
  echo "<tr><td colspan=7><input type=submit name='problem2contest' value='CheckToNewContest'>";
  echo "<tr><td>PID<td>Title<td>Date";
  if($GE_TA){
          if($GE_TA)   echo "<td>Status<td>Delete";
          echo "<td>Edit<td>TestData</tr>";
  }
  for (;$row=mysql_fetch_object($result);){
          echo "<tr>";
          echo "<td>".$row->problem_id;
          echo "<input type=checkbox name='pid[]' value='$row->problem_id'>";
          echo "<td><a href='../problem.php?id=$row->problem_id'>".$row->title."</a>";
          echo "<td>".$row->in_date;
          if($GE_TA){
                  if($GE_TA){
                          echo "<td><a href=problem_df_change.php?id=$row->problem_id&getkey=".$_SESSION['getkey'].">"
                          .($row->defunct=="N"?"<span titlc='click to reserve it' class=green>Available</span>":"<span class=red title='click to be available'>Reserved</span>")."</a><td>";
                          if($OJ_SAE||function_exists("system")){
                                ?>
                                <a href='#' onclick='javascript:if(confirm("Delete?")) location.href="problem_del.php?id=<?php echo $row->problem_id?>&getkey=<?php echo $_SESSION['getkey']?>";'>
                                Delete</a>
                                <?php
                          }
                  }
                  if($GE_TA){
                          echo "<td><a href=problem_edit.php?id=$row->problem_id&getkey=".$_SESSION['getkey'].">Edit</a>";
                          echo "<td><a href=quixplorer/index.php?action=list&dir=$row->problem_id&order=name&srt=yes>TestData</a>";
                  }
          }
          echo "</tr>";
  }
  echo "<tr><td colspan=7><input type=submit name='problem2contest' value='CheckToNewContest'>";
  echo "</tr></form>";
  echo "</table></center>";
  require("../oj-footer.php");
?>