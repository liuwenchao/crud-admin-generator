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

$app->match('/boom/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
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
    
    $searchValue = $search['value'];
    $orderValue = $order[0];
    
    $orderClause = "";
    if($orderValue) {
        $orderClause = " ORDER BY ". $columns[(int)$orderValue['column']]['data'] . " " . $orderValue['dir'];
    }
    
    $table_columns = array(
		'id', 
		'name', 
		'code', 
		'product_id', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'varchar(8)', 
		'char(12)', 
		'int(11)', 

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
    
    $recordsTotal = $app['db']->fetchColumn("SELECT COUNT(*) FROM `boom`" . $whereClause . $orderClause, array(), 0);
    
    $find_sql = "SELECT * FROM `boom`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

			if($table_columns[$i] == 'product_id'){
			    $findexternal_sql = 'SELECT `id` FROM `product` WHERE `id` = ?';
			    $findexternal_row = $app['db']->fetchAssoc($findexternal_sql, array($row_sql[$table_columns[$i]]));
			    $rows[$row_key][$table_columns[$i]] = $findexternal_row['id'];
			}
			else if( $table_columns_type[$i] != "blob") {
			    $rows[$row_key][$table_columns[$i]] = $row_sql[$table_columns[$i]];
			} else {
				if( !$row_sql[$table_columns[$i]] ) {
					$rows[$row_key][$table_columns[$i]] = "";
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
$app->match('/boom/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . boom . " WHERE ".$idfldname." = ?";
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



$app->match('/boom', function () use ($app) {
    
	$table_columns = array(
		'id', 
		'name', 
		'code', 
		'product_id', 

    );

    $primary_key = "id";	

    return $app['twig']->render('boom/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('boom_list');



$app->match('/boom/create', function () use ($app) {
    
    $initial_data = array(
		'name' => '', 
		'code' => '', 
		'product_id' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);

	$options = array();
	$findexternal_sql = 'SELECT `id`, `id` FROM `product`';
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['id']] = $findexternal_row['id'];
	}
	if(count($options) > 0){
	    $form = $form->add('product_id', 'choice', array(
	        'required' => true,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    ));
	}
	else{
	    $form = $form->add('product_id', 'text', array('required' => true));
	}


	$form = $form->add('name', 'text', array('required' => true));
	$form = $form->add('code', 'text', array('required' => true));

$table_columns = array(
		'id', 
		'name', 
		'code', 
		'product_id', 

);

$table_columns_type = array(
		'int(11)', 
		'varchar(8)', 
		'char(12)', 
		'int(11)', 

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

            $update_query = "INSERT INTO `boom` (`name`, `code`, `product_id`) VALUES (?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['name'], $data['code'], $data['product_id']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'boom created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('boom_list'));

        }
    }

    return $app['twig']->render('boom/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('boom_create');



$app->match('/boom/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `boom` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('boom_list'));
    }

    
    $initial_data = array(
		'name' => $row_sql['name'], 
		'code' => $row_sql['code'], 
		'product_id' => $row_sql['product_id'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);

	$options = array();
	$findexternal_sql = 'SELECT `id`, `id` FROM `product`';
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['id']] = $findexternal_row['id'];
	}
	if(count($options) > 0){
	    $form = $form->add('product_id', 'choice', array(
	        'required' => true,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    ));
	}
	else{
	    $form = $form->add('product_id', 'text', array('required' => true));
	}


	$form = $form->add('name', 'text', array('required' => true));
	$form = $form->add('code', 'text', array('required' => true));

$table_columns = array(
		'id', 
		'name', 
		'code', 
		'product_id', 

);

$table_columns_type = array(
		'int(11)', 
		'varchar(8)', 
		'char(12)', 
		'int(11)', 

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

            $update_query = "UPDATE `boom` SET `name` = ?, `code` = ?, `product_id` = ? WHERE `id` = ?";
            $app['db']->executeUpdate($update_query, array($data['name'], $data['code'], $data['product_id'], $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'boom edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('boom_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('boom/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('boom_edit');


$app->match('/boom/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `boom` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `boom` WHERE `id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'boom deleted!',
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

    return $app->redirect($app['url_generator']->generate('boom_list'));

})
->bind('boom_delete');



$app->match('/boom/downloadList', function (Symfony\Component\HttpFoundation\Request $request) use($app){
    
    $table_columns = array(
		'id', 
		'name', 
		'code', 
		'product_id', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'varchar(8)', 
		'char(12)', 
		'int(11)', 

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
     
    $find_sql = "SELECT ".$columns_to_select." FROM `boom`";
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
})->bind('boom_downloadList');



