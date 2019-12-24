<?php

/*
 * This file is part of the CRUD Admin Generator project.
 *
 * Author: Jon Segador <jonseg@gmail.com>
 * Web: http://crud-admin-generator.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../src/app.php';
require_once __DIR__.'/../../../src/utils.php';


use Symfony\Component\Validator\Constraints as Assert;

$app->match('/trademark/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
    $start = 0;
    $vars = $request->request->all();
    $qsStart = (int)$vars["start"];
    $search = $vars["search"];
    $order = $vars["order"];
    $columns = $vars["columns"];
    $qsLength = (int)$vars["length"];    
    
    if($qsStart) {
        $start = $qsStart;
    }    
	
    $index = $start;   
    $rowsPerPage = $qsLength;
       
    $rows = array();
    
    $searchValue = str_replace("'","",$search['value']);
    $orderValue = $order[0];
    
    $orderClause = "";
    if($orderValue) {
        $orderClause = " ORDER BY trademark.". $columns[(int)$orderValue['column']]['data'] . " " . $orderValue['dir'];
    }
    
    $table_columns = array(
		'id', 
		'name', 
		'logo', 
		'number', 
		'owner', 
		'category', 
		'apply_date', 
		'publish_date', 
		'register_date', 
		'scope', 
		'status', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'varchar(20)', 
		'blob', 
		'varchar(10)', 
		'varchar(255)', 
		'tinyint(4)', 
		'date', 
		'date', 
		'date', 
		'text', 
		'varchar(63)', 

    );    
    
    $whereClause = "";
    
    $i = 0;
    foreach($table_columns as $col){
        
        if ($i == 0) {
           $whereClause = " WHERE";
        }
        
        if ($i > 0) {
            $whereClause =  $whereClause . " OR"; 
        }
        
        $whereClause =  $whereClause . " trademark." . $col . " LIKE '%". $searchValue ."%'";
        $i = $i + 1;
    }
    $whereClause .=  "";
    
    $recordsTotal = $app['db']->fetchColumn("SELECT COUNT(*) FROM `trademark` " . $whereClause . $orderClause, array(), 0);
    
    $find_sql = "SELECT * FROM `trademark` ". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

		if( $table_columns_type[$i] != "blob") {
				$rows[$row_key][$table_columns[$i]] = $row_sql[$table_columns[$i]];
		} else {
				if( !$row_sql[$table_columns[$i]] ) {
						$rows[$row_key][$table_columns[$i]] = "0 Kb.";
				} else {
					foreach (explode(',',$row_sql[$table_columns[$i]]) as $img) {
						$image_url = "/resources/files/" . $img;
						$rows[$row_key][$table_columns[$i]] .= " <a target='__blank' href='$image_url'><img style='width:40px;' src='$image_url'/></a>";
					}
				}
		}

        }
    }    
    
    $queryData = new queryData();
    $queryData->start = $start;
    $queryData->recordsTotal = $recordsTotal;
    $queryData->recordsFiltered = $recordsTotal;
    $queryData->data = $rows;
    
    return new Symfony\Component\HttpFoundation\Response(json_encode($queryData), 200);
});




/* Download blob img */
$app->match('/trademark/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . trademark . " WHERE ".$idfldname." = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($rowid));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('menu_list'));
    }

    header('Content-Description: File Transfer');
    header('Content-Type: image/jpeg');
    header("Content-length: ".strlen( $row_sql[$fieldname] ));
    header('Expires: 0');
    header('Cache-Control: public');
    header('Pragma: public');
    ob_clean();    
    echo $row_sql[$fieldname];
    exit();
   
    
});



$app->match('/trademark', function () use ($app) {
    
	$table_columns = array(
		'id', 
		'name', 
		'logo', 
		'number', 
		'owner', 
		'category', 
		'apply_date', 
		'publish_date', 
		'register_date', 
		'scope', 
		'status', 

    );

    $table_label_columns = array(
		'id', 
		'名字', 
		'图片', 
		'商标号', 
		'申请人', 
		'类别', 
		'申请日期', 
		'初审公告日期', 
		'注册日期', 
		'使用商品范围', 
		'状态', 

    );

    $primary_key = "id";	

    return $app['twig']->render('trademark/list.html.twig', array(
    	"table_columns" => $table_columns,
    	"table_label_columns" => $table_label_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('trademark_list');



$app->match('/trademark/create', function () use ($app) {
    
    $initial_data = array(
		'name' => '', 
		'logo' => '', 
		'number' => '', 
		'owner' => '', 
		'category' => '', 
		'apply_date' => '2019-10-01', 
		'publish_date' => '2019-10-01', 
		'register_date' => '2019-10-01', 
		'scope' => '', 
		'status' => '申请中', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('name', 'text', array('required' => true, 'label' => '名字'));
	$form = $form->add('logo', 'file', array('required' => true, 'label' => '图片', 'data_class' => null));
	$form = $form->add('number', 'text', array('required' => true, 'label' => '商标号'));
	$form = $form->add('owner', 'text', array('required' => true, 'label' => '申请人'));
	$form = $form->add('category', 'text', array('required' => true, 'label' => '类别'));
	$form = $form->add('apply_date', 'text', array('required' => true, 'label' => '申请日期'));
	$form = $form->add('publish_date', 'text', array('required' => true, 'label' => '初审公告日期'));
	$form = $form->add('register_date', 'text', array('required' => true, 'label' => '注册日期'));
	$form = $form->add('scope', 'textarea', array('required' => false, 'label' => '使用商品范围'));
	$form = $form->add('status', 'text', array('required' => true, 'label' => '状态'));

$table_columns = array(
		'id', 
		'name', 
		'logo', 
		'number', 
		'owner', 
		'category', 
		'apply_date', 
		'publish_date', 
		'register_date', 
		'scope', 
		'status', 

);

$table_columns_type = array(
		'int(11)', 
		'varchar(20)', 
		'blob', 
		'varchar(10)', 
		'varchar(255)', 
		'tinyint(4)', 
		'date', 
		'date', 
		'date', 
		'text', 
		'varchar(63)', 

); 

    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            foreach ($table_columns_type as $key => $value) {
                if(in_array($value, array('blob'))){
                    $column = $table_columns[$key];
                    if ($file = $form[$column]->getData()) {
                        $newFilename = uniqid().'.'.$file->guessExtension();
                        // Move the file to resources directory
                        try {
                            $file->move(
                                'resources/files/',
                                $newFilename
                            );
                        } catch (FileException $e) {
                            //TODO ... handle exception if something happens during file upload
                        }
                        $data[$column] = $newFilename;
                    }
                }
            }

            $update_query = "INSERT INTO `trademark` (`name`, `logo`, `number`, `owner`, `category`, `apply_date`, `publish_date`, `register_date`, `scope`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['name'], $data['logo'], $data['number'], $data['owner'], $data['category'], $data['apply_date'], $data['publish_date'], $data['register_date'], $data['scope'], $data['status']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'trademark created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('trademark_list'));

        }
    }

    return $app['twig']->render('trademark/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('trademark_create');



$app->match('/trademark/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `trademark` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('trademark_list'));
    }

    
    $initial_data = array(
		'name' => $row_sql['name'], 
		'logo' => $row_sql['logo'], 
		'number' => $row_sql['number'], 
		'owner' => $row_sql['owner'], 
		'category' => $row_sql['category'], 
		'apply_date' => $row_sql['apply_date'], 
		'publish_date' => $row_sql['publish_date'], 
		'register_date' => $row_sql['register_date'], 
		'scope' => $row_sql['scope'], 
		'status' => $row_sql['status'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('name', 'text', array('required' => true, 'label' => '名字'));
	$form = $form->add('logo', 'file', array('required' => true, 'label' => '图片', 'data_class' => null));
	$form = $form->add('number', 'text', array('required' => true, 'label' => '商标号'));
	$form = $form->add('owner', 'text', array('required' => true, 'label' => '申请人'));
	$form = $form->add('category', 'text', array('required' => true, 'label' => '类别'));
	$form = $form->add('apply_date', 'text', array('required' => true, 'label' => '申请日期'));
	$form = $form->add('publish_date', 'text', array('required' => true, 'label' => '初审公告日期'));
	$form = $form->add('register_date', 'text', array('required' => true, 'label' => '注册日期'));
	$form = $form->add('scope', 'textarea', array('required' => false, 'label' => '使用商品范围'));
	$form = $form->add('status', 'text', array('required' => true, 'label' => '状态'));

$table_columns = array(
		'id', 
		'name', 
		'logo', 
		'number', 
		'owner', 
		'category', 
		'apply_date', 
		'publish_date', 
		'register_date', 
		'scope', 
		'status', 

);

$table_columns_type = array(
		'int(11)', 
		'varchar(20)', 
		'blob', 
		'varchar(10)', 
		'varchar(255)', 
		'tinyint(4)', 
		'date', 
		'date', 
		'date', 
		'text', 
		'varchar(63)', 

); 

    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            foreach ($table_columns_type as $key => $value) {
                if(in_array($value, array('blob'))){
                    $column = $table_columns[$key];
                    if ($file = $form[$column]->getData()) {
                        $newFilename = uniqid().'.'.$file->guessExtension();
                        // Move the file to resources directory
                        try {
                            $file->move(
                                'resources/files/',
                                $newFilename
                            );
                        } catch (FileException $e) {
                            //TODO ... handle exception if something happens during file upload
                        }
                        $data[$column] = $newFilename . ',' . $row_sql[$column];
                    } else {
                        $data[$column] = $row_sql[$column];
                    }
                }
            }

            $update_query = "UPDATE `trademark` SET `name` = ?, `logo` = ?, `number` = ?, `owner` = ?, `category` = ?, `apply_date` = ?, `publish_date` = ?, `register_date` = ?, `scope` = ?, `status` = ? WHERE `id` = ?";
            $app['db']->executeUpdate($update_query, array($data['name'], $data['logo'], $data['number'], $data['owner'], $data['category'], $data['apply_date'], $data['publish_date'], $data['register_date'], $data['scope'], $data['status'], $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'trademark edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('trademark_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('trademark/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('trademark_edit');


$app->match('/trademark/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `trademark` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `trademark` WHERE `id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'trademark deleted!',
            )
        );
    }
    else{
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );  
    }

    return $app->redirect($app['url_generator']->generate('trademark_list'));

})
->bind('trademark_delete');



$app->match('/trademark/downloadList', function (Symfony\Component\HttpFoundation\Request $request) use($app){
    
    $table_columns = array(
		'id', 
		'name', 
		'logo', 
		'number', 
		'owner', 
		'category', 
		'apply_date', 
		'publish_date', 
		'register_date', 
		'scope', 
		'status', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'varchar(20)', 
		'blob', 
		'varchar(10)', 
		'varchar(255)', 
		'tinyint(4)', 
		'date', 
		'date', 
		'date', 
		'text', 
		'varchar(63)', 

    );   

    $types_to_cut = array('blob');
    $index_of_types_to_cut = array();
    foreach ($table_columns_type as $key => $value) {
        if(in_array($value, $types_to_cut)){
            unset($table_columns[$key]);
        }
    }

    $columns_to_select = implode(',', array_map(function ($row){
        return '`'.$row.'`';
    }, $table_columns));
     
    $find_sql = "SELECT ".$columns_to_select." FROM `trademark`";
    $rows_sql = $app['db']->fetchAll($find_sql, array());
  
    $mpdf = new mPDF();

    $stylesheet = file_get_contents('../web/resources/css/bootstrap.min.css'); // external css
    $mpdf->WriteHTML($stylesheet,1);
    $mpdf->WriteHTML('.table {
    border-radius: 5px;
    width: 100%;
    margin: 0px auto;
    float: none;
}',1);

    $mpdf->WriteHTML(build_table($rows_sql));
    $mpdf->Output();
})->bind('trademark_downloadList');



