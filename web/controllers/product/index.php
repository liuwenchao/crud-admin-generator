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

$app->match('/product/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
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
        $orderClause = " ORDER BY a.". $columns[(int)$orderValue['column']]['data'] . " " . $orderValue['dir'];
    }
    
    $table_columns = array(
		'id', 
		'provider_id', 
		'category_id', 
		'size', 
		'unit', 
		'category2', 
		'package_code', 
		'bottle', 
		'material_id', 
		'minimal_order', 
		'pre_price', 
		'full_price', 
		'open_mould_period', 
		'sample_period', 
		'payment_method', 
		'supply_period', 
		'memo', 
		'brand_id', 
		'code', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'int(11)', 
		'char(1)', 
		'int(11)', 
		'varchar(2)', 
		'varchar(255)', 
		'char(14)', 
		'blob', 
		'char(2)', 
		'int(11)', 
		'decimal(8,2)', 
		'decimal(8,2)', 
		'varchar(8)', 
		'varchar(8)', 
		'varchar(8)', 
		'varchar(8)', 
		'text', 
		'int(11)', 
		'char(8)', 

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
        
        $whereClause =  $whereClause . " a." . $col . " LIKE '%". $searchValue ."%'";
        
        $i = $i + 1;
	}
	//TODO optimize the sql performance here.
	$whereClause =  $whereClause . " OR b.name LIKE '%". $searchValue ."%'";
	$whereClause =  $whereClause . " OR c.name LIKE '%". $searchValue ."%'";
	$whereClause =  $whereClause . " OR d.name LIKE '%". $searchValue ."%'";
	$whereClause =  $whereClause . " OR e.name LIKE '%". $searchValue ."%'";
	
    $recordsTotal = $app['db']->fetchColumn("SELECT COUNT(*) FROM `product` a inner join provider b on a.provider_id = b.id inner join category c on a.category_id=c.id inner join material d on a.material_id = d.id inner join brand e on a.brand_id = e.id" . $whereClause . $orderClause, array(), 0);
    
    $find_sql = "SELECT a.* FROM `product` a inner join provider b on a.provider_id = b.id inner join category c on a.category_id=c.id inner join material d on a.material_id = d.id inner join brand e on a.brand_id = e.id". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

			if($table_columns[$i] == 'provider_id'){
			    $findexternal_sql = 'SELECT `name` FROM `provider` WHERE `id` = ?';
			    $findexternal_row = $app['db']->fetchAssoc($findexternal_sql, array($row_sql[$table_columns[$i]]));
			    $rows[$row_key][$table_columns[$i]] = $findexternal_row['name'];
			}
			else if($table_columns[$i] == 'category_id'){
			    $findexternal_sql = 'SELECT `name` FROM `category` WHERE `id` = ?';
			    $findexternal_row = $app['db']->fetchAssoc($findexternal_sql, array($row_sql[$table_columns[$i]]));
			    $rows[$row_key][$table_columns[$i]] = $findexternal_row['name'];
			}
			else if($table_columns[$i] == 'material_id'){
			    $findexternal_sql = 'SELECT `name` FROM `material` WHERE `id` = ?';
			    $findexternal_row = $app['db']->fetchAssoc($findexternal_sql, array($row_sql[$table_columns[$i]]));
			    $rows[$row_key][$table_columns[$i]] = $findexternal_row['name'];
			}
			else if($table_columns[$i] == 'brand_id'){
			    $findexternal_sql = 'SELECT `name` FROM `brand` WHERE `id` = ?';
			    $findexternal_row = $app['db']->fetchAssoc($findexternal_sql, array($row_sql[$table_columns[$i]]));
			    $rows[$row_key][$table_columns[$i]] = $findexternal_row['name'];
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
$app->match('/product/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . product . " WHERE ".$idfldname." = ?";
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



$app->match('/product', function () use ($app) {
    
	$table_columns = array(
		'id', 
		'provider_id', 
		'category_id', 
		'size', 
		'unit', 
		'category2', 
		'package_code', 
		'bottle', 
		'material_id', 
		'minimal_order', 
		'pre_price', 
		'full_price', 
		'open_mould_period', 
		'sample_period', 
		'payment_method', 
		'supply_period', 
		'memo', 
		'brand_id', 
		'code', 

    );

    $primary_key = "id";	

    return $app['twig']->render('product/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('product_list');



$app->match('/product/create', function () use ($app) {
    
    $initial_data = array(
		'provider_id' => '', 
		'category_id' => '', 
		'size' => '', 
		'unit' => '', 
		'category2' => '', 
		'package_code' => '', 
		'bottle' => '', 
		'material_id' => '', 
		'minimal_order' => '', 
		'pre_price' => '', 
		'full_price' => '', 
		'open_mould_period' => '', 
		'sample_period' => '', 
		'payment_method' => '', 
		'supply_period' => '', 
		'memo' => '', 
		'brand_id' => '', 
		'code' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);

	$options = array();
	$findexternal_sql = 'SELECT `id`, `name` FROM `provider`';
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['id']] = $findexternal_row['name'];
	}
	if(count($options) > 0){
	    $form = $form->add('provider_id', 'choice', array(
	        'required' => true,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    ));
	}
	else{
	    $form = $form->add('provider_id', 'text', array('required' => true));
	}

	$options = array();
	$findexternal_sql = 'SELECT `id`, `name` FROM `category`';
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['id']] = $findexternal_row['name'];
	}
	if(count($options) > 0){
	    $form = $form->add('category_id', 'choice', array(
	        'required' => true,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    ));
	}
	else{
	    $form = $form->add('category_id', 'text', array('required' => true));
	}

	$options = array();
	$findexternal_sql = 'SELECT `id`, `name` FROM `material`';
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['id']] = $findexternal_row['name'];
	}
	if(count($options) > 0){
	    $form = $form->add('material_id', 'choice', array(
	        'required' => true,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    ));
	}
	else{
	    $form = $form->add('material_id', 'text', array('required' => true));
	}

	$options = array();
	$findexternal_sql = 'SELECT `id`, `name` FROM `brand`';
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['id']] = $findexternal_row['name'];
	}
	if(count($options) > 0){
	    $form = $form->add('brand_id', 'choice', array(
	        'required' => true,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    ));
	}
	else{
	    $form = $form->add('brand_id', 'text', array('required' => true));
	}

	$form = $form->add('size', 'text', array('required' => true));
	$form = $form->add('unit', 'text', array('required' => true));
	$form = $form->add('category2', 'textarea', array('required' => false));
	$form = $form->add('package_code', 'text', array('required' => false));
	$form = $form->add('bottle', 'file', array('required' => false));
	$form = $form->add('minimal_order', 'text', array('required' => false));
	$form = $form->add('pre_price', 'text', array('required' => true));
	$form = $form->add('full_price', 'text', array('required' => true));
	$form = $form->add('open_mould_period', 'text', array('required' => false));
	$form = $form->add('sample_period', 'text', array('required' => false));
	$form = $form->add('payment_method', 'text', array('required' => false));
	$form = $form->add('supply_period', 'text', array('required' => false));
	$form = $form->add('memo', 'textarea', array('required' => false));
	$form = $form->add('code', 'text', array('required' => true));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
						if ($bottleFile = $form['bottle']->getData()) {
								$newFilename = uniqid().'.'.$bottleFile->guessExtension();

								// Move the file to the directory where brochures are stored
								try {
										$bottleFile->move(
												'resources/files/',
												$newFilename
										);
								} catch (FileException $e) {
										// ... handle exception if something happens during file upload
								}
								$data['bottle'] = $newFilename;
						}
            $update_query = "INSERT INTO `product` (`provider_id`, `category_id`, `size`, `unit`, `category2`, `package_code`, `bottle`, `material_id`, `minimal_order`, `pre_price`, `full_price`, `open_mould_period`, `sample_period`, `payment_method`, `supply_period`, `memo`, `brand_id`, `code`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['provider_id'], $data['category_id'], $data['size'], $data['unit'], $data['category2'], $data['package_code'], $data['bottle'], $data['material_id'], $data['minimal_order'], $data['pre_price'], $data['full_price'], $data['open_mould_period'], $data['sample_period'], $data['payment_method'], $data['supply_period'], $data['memo'], $data['brand_id'], $data['code']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'product created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('product_list'));

        }
    }

    return $app['twig']->render('product/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('product_create');



$app->match('/product/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `product` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('product_list'));
    }

    
    $initial_data = array(
		'provider_id' => $row_sql['provider_id'], 
		'category_id' => $row_sql['category_id'], 
		'size' => $row_sql['size'], 
		'unit' => $row_sql['unit'], 
		'category2' => $row_sql['category2'], 
		'package_code' => $row_sql['package_code'], 
		'bottle' => $row_sql['bottle'], 
		'material_id' => $row_sql['material_id'], 
		'minimal_order' => $row_sql['minimal_order'], 
		'pre_price' => $row_sql['pre_price'], 
		'full_price' => $row_sql['full_price'], 
		'open_mould_period' => $row_sql['open_mould_period'], 
		'sample_period' => $row_sql['sample_period'], 
		'payment_method' => $row_sql['payment_method'], 
		'supply_period' => $row_sql['supply_period'], 
		'memo' => $row_sql['memo'], 
		'brand_id' => $row_sql['brand_id'], 
		'code' => $row_sql['code'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);

	$options = array();
	$findexternal_sql = 'SELECT `id`, `name` FROM `provider`';
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['id']] = $findexternal_row['name'];
	}
	if(count($options) > 0){
	    $form = $form->add('provider_id', 'choice', array(
	        'required' => true,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    ));
	}
	else{
	    $form = $form->add('provider_id', 'text', array('required' => true));
	}

	$options = array();
	$findexternal_sql = 'SELECT `id`, `name` FROM `category`';
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['id']] = $findexternal_row['name'];
	}
	if(count($options) > 0){
	    $form = $form->add('category_id', 'choice', array(
	        'required' => true,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    ));
	}
	else{
	    $form = $form->add('category_id', 'text', array('required' => true));
	}

	$options = array();
	$findexternal_sql = 'SELECT `id`, `name` FROM `material`';
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['id']] = $findexternal_row['name'];
	}
	if(count($options) > 0){
	    $form = $form->add('material_id', 'choice', array(
	        'required' => true,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    ));
	}
	else{
	    $form = $form->add('material_id', 'text', array('required' => true));
	}

	$options = array();
	$findexternal_sql = 'SELECT `id`, `name` FROM `brand`';
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['id']] = $findexternal_row['name'];
	}
	if(count($options) > 0){
	    $form = $form->add('brand_id', 'choice', array(
	        'required' => true,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    ));
	}
	else{
	    $form = $form->add('brand_id', 'text', array('required' => true));
	}


	$form = $form->add('size', 'text', array('required' => true));
	$form = $form->add('unit', 'text', array('required' => true));
	$form = $form->add('category2', 'textarea', array('required' => false));
	$form = $form->add('package_code', 'text', array('required' => false));
	$form = $form->add('bottle', 'file', array('required' => false, 'data_class' => null));
	$form = $form->add('minimal_order', 'text', array('required' => false));
	$form = $form->add('pre_price', 'text', array('required' => true));
	$form = $form->add('full_price', 'text', array('required' => true));
	$form = $form->add('open_mould_period', 'text', array('required' => false));
	$form = $form->add('sample_period', 'text', array('required' => false));
	$form = $form->add('payment_method', 'text', array('required' => false));
	$form = $form->add('supply_period', 'text', array('required' => false));
	$form = $form->add('memo', 'textarea', array('required' => false));
	$form = $form->add('code', 'text', array('required' => true));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();
						if ($bottleFile = $form['bottle']->getData()) {
							$newFilename = uniqid().'.'.$bottleFile->guessExtension();

							// Move the file to the directory where brochures are stored
							try {
									$bottleFile->move(
											'resources/files/',
											$newFilename
									);
							} catch (FileException $e) {
									// ... handle exception if something happens during file upload
							}
							$data['bottle'] = $newFilename . ',' . $row_sql['bottle'];
						} else {
							$data['bottle'] = $row_sql['bottle'];
						}
            $update_query = "UPDATE `product` SET `provider_id` = ?, `category_id` = ?, `size` = ?, `unit` = ?, `category2` = ?, `package_code` = ?, `bottle` = ?, `material_id` = ?, `minimal_order` = ?, `pre_price` = ?, `full_price` = ?, `open_mould_period` = ?, `sample_period` = ?, `payment_method` = ?, `supply_period` = ?, `memo` = ?, `brand_id` = ?, `code` = ? WHERE `id` = ?";
            $app['db']->executeUpdate($update_query, array($data['provider_id'], $data['category_id'], $data['size'], $data['unit'], $data['category2'], $data['package_code'], $data['bottle'], $data['material_id'], $data['minimal_order'], $data['pre_price'], $data['full_price'], $data['open_mould_period'], $data['sample_period'], $data['payment_method'], $data['supply_period'], $data['memo'], $data['brand_id'], $data['code'], $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'product edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('product_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('product/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('product_edit');


$app->match('/product/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `product` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `product` WHERE `id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'product deleted!',
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

    return $app->redirect($app['url_generator']->generate('product_list'));

})
->bind('product_delete');



$app->match('/product/downloadList', function (Symfony\Component\HttpFoundation\Request $request) use($app){
    
    $table_columns = array(
		'id', 
		'provider_id', 
		'category_id', 
		'size', 
		'unit', 
		'category2', 
		'package_code', 
		'bottle', 
		'material_id', 
		'minimal_order', 
		'pre_price', 
		'full_price', 
		'open_mould_period', 
		'sample_period', 
		'payment_method', 
		'supply_period', 
		'memo', 
		'brand_id', 
		'code', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'int(11)', 
		'char(1)', 
		'int(11)', 
		'varchar(2)', 
		'varchar(2)', 
		'int(11)', 
		'varchar(2)', 
		'char(14)', 
		'blob', 
		'char(2)', 
		'int(11)', 
		'decimal(8,2)', 
		'decimal(8,2)', 
		'varchar(8)', 
		'varchar(8)', 
		'varchar(8)', 
		'varchar(8)', 
		'text', 
		'int(11)', 
		'char(8)', 

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
     
    $find_sql = "SELECT ".$columns_to_select." FROM `product`";
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
})->bind('product_downloadList');



