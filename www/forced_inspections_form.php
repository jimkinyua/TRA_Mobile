<?php 
//require '/server/DB_PARAMS/connect.php';
// require('/server/DB_PARAMS/connect.php');
// require_once('utilities.php');
// require_once('GlobalFunctions.php');

include('../server/DB_PARAMS/connect.php');

// include($_SERVER['DOCUMENT_ROOT'].'/server/DB_PARAMS/connect.php');


?>

<div class="fluent-menu" data-role="fluentmenu">

    <ul class="tabs-holder">
        <li class="active"><a href="#tab_licence" onClick="return false;">Forced Inspections</a></li>
       <!--  <li class=""><a href="#tab_inspections">Inspections</a></li> -->

    </ul>

    <div class="tabs-content" style="height: auto; padding: 2rem">
        <div class="tab-panel" id="tab_licence">

            <legend><span id="name">Client  Details</span></legend>
    <form method="post">

                <fieldset>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center" style="color:#F00"><div id="msg"></div></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td width="50%">
                    
                    <label>Customer Name</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="CustomerName" type="text" id="CustomerName" placeholder="" >
                    </div>


                   <label>Contact Person</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="ContactPerson" type="text" id="ContactPerson" placeholder="" >
                        
                    </div>
                    
                    <!-- <label>Business Type</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="Type" type="text" id="Type" placeholder="" >
                    </div> -->

                      <label>Business Type</label>      
                      <div class="input-control select">
                      <select name="Type" id="Type">
                      <option value="Individual">Individual</option>
                      <option value="Business">Business</option>              
                      </select>
                      </div>


                    <label>Postal Address</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="PostalAddress" type="text" id="PostalAddress" placeholder="" >
                        
                    </div>
                    
                    <label>Physical Address</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="PhysicalAddress" type="text" id="PhysicalAddress" placeholder="" >
                        
                    </div>
                    
                    <label>Plot No</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="PlotNo" type="text" id="PlotNo" placeholder="" >
                        
                    </div>
                    <label>Post Code</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="PostalCode" type="text" id="PostalCode" placeholder="" >
                    </div>
                    
                    <label>Town</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="Town" type="text" id="Town" placeholder="" >
                        
                    </div>


                  <label>Services</label>
                  <div class="input-control select" data-role="input-control">
                    <select name="ServiceID"  id="ServiceID">
                            <option value="0" selected="selected"></option>
                                <?php

                                $s_sql = "SELECT * FROM Services ORDER BY ServiceID";
                                
                                $s_result = sqlsrv_query($db, $s_sql);
                                if ($s_result) 
                                { //connection succesful 
                                    while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
                                    {
                                        $s_id = $row["ServiceID"];
                                        $s_name = $row["ServiceName"];
                                        if ($ServiceID==$s_id) 
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
                  
                 </div>


                    <label>Permit No(If licensed)</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="PermitNo" type="text" id="PermitNo" placeholder="" >
                        
                    </div>

                    
                  
                   
                    

        </td>
    <td width="50%" valign="top"><div id="info" style="padding-left:20px">
    
                    <label>Telephone 1</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="Telephone1" type="text" id="Telephone1" placeholder="" >
                        
                    </div>
                    
                    <label>Telephone 2</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="Telephone2" type="text" id="Telephone2" placeholder="" >
                        
                    </div>

                    <label>Mobile 1</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="Mobile1" type="text" id="Mobile1" placeholder="" >
                        
                    </div>
                     <label>Mobile 2</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="Mobile2" type="text" id="Mobile2" placeholder="" >
                        
                    </div>
                    
                    <label>Email</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="Email" type="text" id="Email" placeholder="" >
                        
                    </div>
                     <label>Website</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="website" type="text" id="website" placeholder="" >
                        
                    </div>
                    
                     <label>PIN</label>
                    <div class="input-control text" data-role="input-control">
                        <input name="PIN" type="text" id="PIN" placeholder="" 
                    </div>
                                   <label>Services Category</label>
                  <div class="input-control select" data-role="input-control">
                    <select name="ServiceCategoryID"  id="ServiceCategoryID">
                            <option value="0" selected="selected"></option>
                                <?php

                                $s_sql = "SELECT * FROM ServiceCategory ORDER BY ServiceCategoryID";
                                
                                $s_result = sqlsrv_query($db, $s_sql);
                                if ($s_result) 
                                { //connection succesful 
                                    while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
                                    {
                                        $s_id = $row["ServiceCategoryID"];
                                        $s_name = $row["CategoryName"];
                                        if ($ServiceCategoryID==$s_id) 
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
                  
                 </div>


                                                    <label>Business Zone(Region)</label>
                  <div class="input-control select" data-role="input-control">
                    <select name="RegionID"  id="RegionID">
                            <option value="0" selected="selected"></option>
                                <?php

                                $s_sql = "SELECT * FROM Regions ORDER BY RegionID";
                                
                                $s_result = sqlsrv_query($db, $s_sql);
                                if ($s_result) 
                                { //connection succesful 
                                    while ($row = sqlsrv_fetch_array( $s_result, SQLSRV_FETCH_ASSOC))
                                    {
                                        $s_id = $row["RegionID"];
                                        $s_name = $row["RegionName"];
                                        if ($RegionID==$s_id) 
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
                  
                 </div>
                    <input type="hidden" name="Force_inspection" id="Force_inspection" value="1" />
                    
                   
    </div>             
    </td>
  </tr>
</table>

<div style="margin-top: 20px" align="left">  <br>

            <div class="place-right"><input name="submit2" type="button" id="submit3" value="Submit" onclick="submitForcedInspections(this.form)" ></div>
</div>

 </fieldset>

            </form>  
                      
        </div>       
                                                            
    </div>  
</div> 
