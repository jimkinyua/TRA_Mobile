<?php 
require 'DB_PARAMS/connect.php';
require_once('utilities.php');
if (!isset($_SESSION))
{
	session_start();
}

$msg ='';
$CreatedUserID = $_SESSION['UserID'];

if (isset($_REQUEST['msg']))
{
	$msg = $_REQUEST['msg'];	
}

$UserID='0';
$UserName='';
$UserFullNames='';
$pfno='';
$idno='';
$email='';
$Mobile='';
$Email='';
$Url='';
$RoleCenterID='';


if (isset($_REQUEST['CustomerID'])) {
     $AgentID = $_REQUEST['AgentID']; 

    $sql = "select ag.*,ag.FirstName+' '+ag.MiddleName+' '+ag.LastName Names,isnull(u.UserID,0)UserID,u.RoleCenterID,u.UserStatusID,u.PfNo 
    from Users u 
            right join agents ag on u.AgentID=ag.AgentID 
            where ag.AgentID = $AgentID 
            order by ag.FirstName+' '+ag.MiddleName+' '+ag.LastName";
            
    $result = sqlsrv_query($db, $sql);

    	while ($myrow = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) 
    {
    	$UserID=$myrow['UserID'];
    	$UserName=$myrow['UserName'];
    	$idno=$myrow['IDNO'];
    	$AgentID=$myrow['AgentID'];
    	$pfno=$myrow['PFNo'];	
    	$Mobile=$myrow['Mobile'];
        $PfNo=$myrow['PfNo'];
        $AgentNames=$myrow['Names'];
    	$Email=$myrow['Email'];	
    	$RoleCenterID=$myrow['RoleCenterID'];
        $UserStatusID=$myrow['UserStatusID'];
    }	
}

?>

	  <legend>Details</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="3">
	       <tr>
			  <td colspan="2" align="center" style="color:#F00"><?php echo $msg; ?></td>
	       </tr>
			<tr>
				<td>
    				<label>Names </label>
                     <div class="input-control text" data-role="input-control">
                            <input name="AgentName" type="text" id="AgentName" value="<?php echo $AgentNames; ?>" disabled>
                            <button class="btn-clear" tabindex="-1"></button>
                    </div>
				</td>
				<td>
                <label>ID No</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="idno" type="text" id="idno" value="<?php echo $idno; ?>" disabled>
                        <button class="btn-clear" tabindex="-1"></button>
              </div>	
				</td> 	
          	</tr>
			<tr>
                <td width="50%">
                	<label>UserName</label>
                	<div class="input-control text" data-role="input-control">
                        <input name="UserName" type="text" id="UserName" value="<?php echo $UserName; ?>" disabled>
                        <button class="btn-clear" tabindex="-1"></button>
                  </div>
				</td>			
                <td width="50%">
               	  <label>Mobile</label>
               	  <div class="input-control text" data-role="input-control">
                   	  <input name="Mobile" type="text" id="Mobile" value="<?php echo $Mobile; ?>" disabled>
                        <button class="btn-clear" tabindex="-1"></button>
                  </div>
                </td>
          	</tr> 			
			<tr>
                <td width="50%">
                <label>PF No</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="pfno" type="text" id="pfno" value="<?php echo $PfNo; ?>" disabled>
                        <button class="btn-clear" tabindex="-1"></button>
                    </div>	
                </td>
                <td width="50%">
                	<label>Official Email</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="official_email" type="text" id="official_email" value="<?php echo $Email; ?>" disabled>
                        <button class="btn-clear" tabindex="-1"></button>
                  </div>
				</td>				

          	</tr>
 			<tr>
            <td><label>Role Center</label>
                    <div class="input-control select" data-role="input-control">
                    	<select name="RoleCenterID"  id="RoleCenterID">
                        <option value="0" selected="selected"></option>
                        <?php 
                        $s_sql = "select * from RoleCenters order by RoleCenterID";
                        $s_result = sqlsrv_query($db, $s_sql);
                        if ($s_result) 
                        { //connection succesful 
                            while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
                            {
                                $s_id = $row["RoleCenterID"];
                                $s_name = $row["RoleCenterName"];
                                if ($RoleCenterID==$s_id) 
                                {
                                    $selected = 'selected="selected"';
                                } else
                                {
                                    $selected = '';
                                }												
                             ?>
                        <option value="<?php echo $s_id; ?>" <?php echo $selected; ?>><?php echo $s_name; ?></option>
                        <?php 
                            }
                        }
                        ?>
                      </select>                       
                  </div></td>
			  <td><label>Status</label>
                    <div class="input-control select" data-role="input-control">
                    	<select name="UserStatusID"  id="UserStatusID">
                        <option value="0" selected="selected"></option>
                        <?php 
                        $s_sql = "SELECT * FROM UserStatus ORDER BY UserStatusName";
						
                        $s_result = sqlsrv_query($db, $s_sql);
                        if ($s_result) 
                        { //connection succesful 
                            while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
                            {
                                $s_id = $row["UserStatusID"];
                                $s_name = $row["UserStatusName"];
                                if ($UserStatusID==$s_id) 
                                {
                                    $selected = 'selected="selected"';
                                } else
                                {
                                    $selected = '';
                                }												
                             ?>
                        <option value="<?php echo $s_id; ?>" <?php echo $selected; ?>><?php echo $s_name; ?></option>
                        <?php 
                            }
                        }
                        ?>
                      </select>
                    
                  </div></td>             
          	</tr>
			                       
                     
        </table>
		<input name="Button" type="button" onclick="loadmypage('users_list.php?'+
        '&email='+this.form.official_email.value+
        '&idno='+this.form.idno.value+
        '&UserName='+this.form.UserName.value+
        '&Mobile='+this.form.Mobile.value+
        '&pfno='+this.form.pfno.value+
        '&UserStatusID='+this.form.UserStatusID.value+
        '&RoleCenterID='+this.form.RoleCenterID.value+
		'&AgentID='+<?php echo $AgentID; ?>+
        '&UserID='+<?php echo $UserID; ?>+
        '&save=1','content','loader','listpages','','users')" value="Save">
      <input type="reset" value="Cancel" onClick="loadmypage('users_list.php?i=1','content','loader','listpages','','users')">					 
      									 
        <span class="table_text">
        
        <input name="add" type="hidden" id="add" value="<?php echo $new;?>" />
        <input name="edit" type="hidden" id="edit" value="<?php echo $edit;?>" />
        		</span>
        <div style="margin-top: 20px">
</div>