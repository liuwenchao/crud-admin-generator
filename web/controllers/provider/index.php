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

$app->match('/provider/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
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
        $orderClause = " ORDER BY ". $columns[(int)$orderValue['column']]['data'] . " " . $orderValue['dir'];
    }
    
    $table_columns = array(
		'id', 
		'code', 
		'name', 
		'license', 
		'address', 
		'contact', 
		'phone', 
		'product', 
		'background', 
		'technology', 
		'capacity', 
		'price', 
		'net', 
		'service', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'varchar(5)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(15)', 
		'varchar(255)', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 

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
        
        $whereClause =  $whereClause . " " . $col . " LIKE '%". $searchValue ."%'";
        
        $i = $i + 1;
    }
    
    $recordsTotal = $app['db']->fetchColumn("SELECT COUNT(*) FROM `provider`" . $whereClause . $orderClause, array(), 0);
    
    $find_sql = "SELECT * FROM `provider`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
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
$app->match('/provider/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . provider . " WHERE ".$idfldname." = ?";
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



$app->match('/provider', function () use ($app) {
    
	$table_columns = array(
		'id', 
		'code', 
		'name', 
		'license', 
		'address', 
		'contact', 
		'phone', 
		'product', 
		'background', 
		'technology', 
		'capacity', 
		'price', 
		'net', 
		'service', 

    );

    $table_label_columns = array(
		'id', 
		'编码', 
		'名字', 
		'营业执照编号', 
		'地址', 
		'联系人', 
		'电话', 
		'主营产品', 
		'工厂背景', 
		'研发实力', 
		'产能', 
		'价格', 
		'账期', 
		'服务', 

    );

    $primary_key = "id";	

    return $app['twig']->render('provider/list.html.twig', array(
    	"table_columns" => $table_columns,
    	"table_label_columns" => $table_label_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('provider_list');



$app->match('/provider/create', function () use ($app) {
    
    $initial_data = array(
		'code' => '', 
		'name' => '', 
		'license' => '', 
		'address' => '', 
		'contact' => '', 
		'phone' => '', 
		'product' => '', 
		'background' => '', 
		'technology' => '', 
		'capacity' => '', 
		'price' => '', 
		'net' => '', 
		'service' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('code', 'text', array('required' => true, 'label' => '编码'));
	$form = $form->add('name', 'text', array('required' => true, 'label' => '名字'));
	$form = $form->add('license', 'text', array('required' => false, 'label' => '营业执照编号'));
	$form = $form->add('address', 'text', array('required' => true, 'label' => '地址'));
	$form = $form->add('contact', 'text', array('required' => true, 'label' => '联系人'));
	$form = $form->add('phone', 'text', array('required' => true, 'label' => '电话'));
	$form = $form->add('product', 'text', array('required' => true, 'label' => '主营产品'));
	$form = $form->add('background', 'text', array('required' => true, 'label' => '工厂背景'));
	$form = $form->add('technology', 'text', array('required' => true, 'label' => '研发实力'));
	$form = $form->add('capacity', 'text', array('required' => true, 'label' => '产能'));
	$form = $form->add('price', 'text', array('required' => true, 'label' => '价格'));
	$form = $form->add('net', 'text', array('required' => true, 'label' => '账期'));
	$form = $form->add('service', 'text', array('required' => true, 'label' => '服务'));

$table_columns = array(
		'id', 
		'code', 
		'name', 
		'license', 
		'address', 
		'contact', 
		'phone', 
		'product', 
		'background', 
		'technology', 
		'capacity', 
		'price', 
		'net', 
		'service', 

);

$table_columns_type = array(
		'int(11)', 
		'varchar(5)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(15)', 
		'varchar(255)', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 

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

            $update_query = "INSERT INTO `provider` (`code`, `name`, `license`, `address`, `contact`, `phone`, `product`, `background`, `technology`, `capacity`, `price`, `net`, `service`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['code'], $data['name'], $data['license'], $data['address'], $data['contact'], $data['phone'], $data['product'], $data['background'], $data['technology'], $data['capacity'], $data['price'], $data['net'], $data['service']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'provider created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('provider_list'));

        }
    }

    return $app['twig']->render('provider/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('provider_create');



$app->match('/provider/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `provider` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('provider_list'));
    }

    
    $initial_data = array(
		'code' => $row_sql['code'], 
		'name' => $row_sql['name'], 
		'license' => $row_sql['license'], 
		'address' => $row_sql['address'], 
		'contact' => $row_sql['contact'], 
		'phone' => $row_sql['phone'], 
		'product' => $row_sql['product'], 
		'background' => $row_sql['background'], 
		'technology' => $row_sql['technology'], 
		'capacity' => $row_sql['capacity'], 
		'price' => $row_sql['price'], 
		'net' => $row_sql['net'], 
		'service' => $row_sql['service'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);


	$form = $form->add('code', 'text', array('required' => true, 'label' => '编码'));
	$form = $form->add('name', 'text', array('required' => true, 'label' => '名字'));
	$form = $form->add('license', 'text', array('required' => false, 'label' => '营业执照编号'));
	$form = $form->add('address', 'text', array('required' => true, 'label' => '地址'));
	$form = $form->add('contact', 'text', array('required' => true, 'label' => '联系人'));
	$form = $form->add('phone', 'text', array('required' => true, 'label' => '电话'));
	$form = $form->add('product', 'text', array('required' => true, 'label' => '主营产品'));
	$form = $form->add('background', 'text', array('required' => true, 'label' => '工厂背景'));
	$form = $form->add('technology', 'text', array('required' => true, 'label' => '研发实力'));
	$form = $form->add('capacity', 'text', array('required' => true, 'label' => '产能'));
	$form = $form->add('price', 'text', array('required' => true, 'label' => '价格'));
	$form = $form->add('net', 'text', array('required' => true, 'label' => '账期'));
	$form = $form->add('service', 'text', array('required' => true, 'label' => '服务'));

$table_columns = array(
		'id', 
		'code', 
		'name', 
		'license', 
		'address', 
		'contact', 
		'phone', 
		'product', 
		'background', 
		'technology', 
		'capacity', 
		'price', 
		'net', 
		'service', 

);

$table_columns_type = array(
		'int(11)', 
		'varchar(5)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(15)', 
		'varchar(255)', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 

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

            $update_query = "UPDATE `provider` SET `code` = ?, `name` = ?, `license` = ?, `address` = ?, `contact` = ?, `phone` = ?, `product` = ?, `background` = ?, `technology` = ?, `capacity` = ?, `price` = ?, `net` = ?, `service` = ? WHERE `id` = ?";
            $app['db']->executeUpdate($update_query, array($data['code'], $data['name'], $data['license'], $data['address'], $data['contact'], $data['phone'], $data['product'], $data['background'], $data['technology'], $data['capacity'], $data['price'], $data['net'], $data['service'], $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'provider edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('provider_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('provider/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('provider_edit');


$app->match('/provider/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `provider` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `provider` WHERE `id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'provider deleted!',
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

    return $app->redirect($app['url_generator']->generate('provider_list'));

})
->bind('provider_delete');



$app->match('/provider/downloadList', function (Symfony\Component\HttpFoundation\Request $request) use($app){
    
    $table_columns = array(
		'id', 
		'code', 
		'name', 
		'license', 
		'address', 
		'contact', 
		'phone', 
		'product', 
		'background', 
		'technology', 
		'capacity', 
		'price', 
		'net', 
		'service', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'varchar(5)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(255)', 
		'varchar(15)', 
		'varchar(255)', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 
		'smallint(5) unsigned', 

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
     
    $find_sql = "SELECT ".$columns_to_select." FROM `provider`";
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
})->bind('provider_downloadList');



