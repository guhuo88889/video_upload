
<?php  

// 设置文件上传的目标目录  
	$targetDir = "uploads/";  
// 检查是否通过POST请求发送了名为'chunk'的文件  
	$chunkFile = isset($_FILES['chunk']) ? $_FILES['chunk']['tmp_name'] : null;  
// 检查是否通过POST请求发送了文件名  
	$filename = isset($_POST['filename']) ? $_POST['filename'] : null;  
// 检查是否通过POST请求发送了文件偏移量  
	$offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;  
// 总块数（totalChunks）
	$totalChunks = isset($_POST['totalChunks']) ? intval($_POST['totalChunks']) : 0;  
//当前块索引（chunkIndex）
	$chunkIndex = isset($_POST['chunkIndex']) ? intval($_POST['chunkIndex']) : 0; 


// 检查是否成功接收到了分块和文件名  
if ($chunkFile && $filename) {  
    // 构造完整的文件路径  
    $filePath = $targetDir . $filename;  
    // 打开目标文件以追加模式，并设置文件指针到指定偏移量  
    $handle = fopen($filePath, "ab"); // 注意：使用"cb"模式，但PHP中更常用"ab"然后fseek，这里为了直接设置偏移量考虑"c"模式，但PHP标准不直接支持"cb"，所以用"ab"后fseek  
    if ($handle) {  
        fseek($handle, $offset);  
        // 打开上传的分块文件的临时文件  
        $chunkHandle = fopen($chunkFile, "rb");  
        if ($chunkHandle) {  
            // 使用stream_copy_to_stream()将分块文件的内容复制到目标文件  
            stream_copy_to_stream($chunkHandle, $handle);  
            // 关闭文件句柄  
            fclose($chunkHandle);  
            fclose($handle);  
            // 反馈上传成功的信息，并返回文件路径 
			 //这里判断是不是最后一个块，如果是则返回json响应信息
				if ($chunkIndex == $totalChunks - 1) {
					
					//自定义新的上传路径
						$newpath = $targetDir . time().$filename;
					//修改上传的文件名为新的文件名，然后将新的文件名路径返回回去(功能：将文件夹中的文件重新命名)
						rename($filePath,$newpath);
					// 发送成功响应，并可能包含一些额外的信息（如最终文件路径）  
						echo json_encode([  
							'success' => true,  
							'message' => "文件上传成功",  
							'filePath' => $newpath  
						],JSON_UNESCAPED_UNICODE);  
				}      
        } else {  
           echo json_encode([  
				   'success' => false,  
				   'message' => "Error opening chunk file."  
                       ],JSON_UNESCAPED_UNICODE);  
            fclose($handle); // 确保关闭已打开的目标文件句柄  
        }  
    } else {  
        // 如果无法打开目标文件，则输出错误信息  
                echo json_encode([  
                    'success' => false,  
                    'message' => "Error opening target file."  
                ],JSON_UNESCAPED_UNICODE);   
    }  
} else {  
    // 如果未能成功接收分块或文件名，则输出错误信息  
        echo json_encode([  
            'success' => false,  
            'message' => "Error uploading chunk."  
        ],JSON_UNESCAPED_UNICODE);    
}  
  
?>



