<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
	}

	public function index()
	{
		$this->load->view('import');
	}

	public function upload(){
		$fileName = time().$_FILES['file']['name'];

		//Config upload
		$config['upload_path'] = './upload/'; //buat folder dengan nama assets di root folder
        $config['file_name'] = $fileName;
        $config['allowed_types'] = 'xls|xlsx|csv';
        $config['max_size'] = 10000;

        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file'))
        {
            $error = array('error' => $this->upload->display_errors());
            print_r($error);
        }else{
            $media = $this->upload->data('file');
            //Upload file excel
        	$inputFileName = './upload/'.$fileName;
        	//reading excel
        	try {
        		$inputFileType = IOFactory::identify($inputFileName);
                $objReader = IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
        	} catch (Exception $e) {
        		die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
        	}

        	$sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            for ($row = 2; $row <= $highestRow; $row++){
            	//  Read a row of data into an array
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                                                NULL,
                                                TRUE,
                                                FALSE);

                //Sesuaikan sama nama kolom tabel di database
                $data = array(
                    /*"idimport"=> $rowData[0][0],*/
                    "nama"=> $rowData[0][0],
                    "alamat"=> $rowData[0][1],
                    "kontak"=> $rowData[0][2]
                );

                //sesuaikan nama dengan nama tabel
                $insert = $this->db->insert("import_data",$data);
            }

            $this->delete_file($media['file_path']);
            echo "Data berhasil ditambahkan";
        }
	}

	function delete_file($file) {
		if (file_exists($file) && is_file($file)){
			unlink($file);
		}
	}
}
