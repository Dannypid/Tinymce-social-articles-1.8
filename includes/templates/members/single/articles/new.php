<?php
global $post, $wpdb, $bp, $socialArticles;

$directWorkflow = isDirectWorkflow();

$statusLabels = array("publish"=>__('Published', 'social-articles'), 
                        "draft"=>__('Draft', 'social-articles'), 
                      "pending"=>__('Under review', 'social-articles'), 
                     "new-post"=>__('New', 'social-articles'));
?>
<?php if(isset($_GET['article'])):    
       $myArticle = get_post($_GET['article']);
       $post_id = $_GET['article'];
       if(isset($myArticle) && $myArticle->post_author == bp_loggedin_user_id() && ($socialArticles->options['allow_author_adition']=="true" || $myArticle->post_status=="draft")){
           $state = "ok";           
           $title = $myArticle->post_title;
           $content = $myArticle->post_content;             
           $status = $myArticle->post_status;
           $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($_GET['article']), 'large');       
           if(isset($large_image_url)){
                $image_name = end(explode("/",$large_image_url[0]));
           }          
           ?>           
            <input type="hidden" id="mode" value="edit"/>
            <input type="hidden" id="feature-image-url" value="<?php echo $large_image_url[0];?>"/>    
           <?php
       }else{          
           $state = "error";
           $message = __("You cannot perform this action", "social-articles");
       }     
       ?>        
<?php else:
       $post_id = 0;  
       $status = "new-post";
       ?>        
       <input type="hidden" id="mode" value="new"/>    
<?php endif;?>

<input type="hidden" id="image-name" value="<?php echo $image_name;?>"/>
<input type="hidden" id="categories-ids"/>
<input type="hidden" id="tag-ids"/>
<input type="hidden" id="tag-names"/>
<input type="hidden" id="categories-names"/>
<input type="hidden" id="post-id" value="<?php echo $post_id;?>"/>   
<input type="hidden" id="post-status" value="<?php echo $status;?>"/>
<input type="hidden" id="direct-workflow" value="<?php echo $directWorkflow;?>"/>

<?php if(!isset($_GET['article']) || $state == "ok"):?>
    <div class="post-save-options messages-container"> 
        <label id="save-message"></label>
        <input type="submit" id="edit-article" class="button" value="<?php _e("Edit article", "social-articles"); ?>" />
        <input type="submit" id="view-article" class="button" value="<?php _e("View article", "social-articles"); ?>" />
        <input type="submit" id="new-article" class="button" value="<?php _e("New article", "social-articles"); ?>" />
    </div>
    <div id="post-maker-container" >
        <div class="options clearfix">
            <div class="options-content">
                <h5><?php _e("Categories", "social-articles"); ?></h5>
                <?php echo get_category_list_options($_GET['article']);?>
            </div>
            <div class="options-content options-content-second">
                <h5><?php _e("Tags", "social-articles"); ?></h5>
                <?php echo get_tags_list_options($_GET['article']);?>
            </div>

            <div class="post-status-container options-content">
                <h5><?php _e("Status", "social-articles"); ?></h5>
                <span class="article-status <?php echo $status;?>"><?php echo $statusLabels[$status];?></span>
            </div>
        </div>

        <h5><?php _e("Article title...", "social-articles"); ?></h5>
        <input type="text" id="post_title" class="title-input" autofocus placeholder="<?php _e("Article title...", "social-articles"); ?>" value="<?php echo $title; ?>"/>
        
        <div class="editor-container">
          <h5><?php _e("Article content...", "social-articles"); ?></h5>      
          <?php
            $editor_id = 'wp_tinymce_editor';
            $settings = array(
              'quicktags'     => TRUE,
              'textarea_rows' => 15,
              'media_buttons' => FALSE,
              'teeny' => TRUE,
              );
            wp_editor( $content, $editor_id, $settings);
          ?>                
       </div>        
        
        <div id="post_image" class="post-image-container">
            <div class="image-preview-container" id="image-preview-container">
            </div>    
            <div class="upload-controls">
                <input id="uploader" type="submit" class="button" value="<?php _e("Upload Image", "social-articles"); ?>" />     
                <label><?php _e("Max size allowed is 2 MB", "social-articles"); ?></label>
            </div>    
            <div class="uploading" id="uploading">
               <img src ="<?php echo SA_BASE_URL;?>/assets/images/load.gif"/>
               <label><?php _e("Uploading your image. Please wait.", "social-articles"); ?></label>
            </div>  
            
            <div class="edit-controls">
                <input type="submit" class="button" value="<?php _e("Delete", "social-articles"); ?>" onclick="cancelImage()" /> 
            </div>    
        </div>

        <div id="save-waiting" class="messages-container">
             <img id="save-gif"src ="<?php echo SA_BASE_URL;?>/assets/images/load.gif"/>
             <label><?php _e("Saving your article. Please wait.", "social-articles"); ?></label>        
        </div>        
        <div id="error-box" class="messages-container">       
        </div>
        <div class="buttons-container" id="create-controls">
            <?php if(($status=="draft" || $status == "new-post") && !$directWorkflow):?>
                <input type="checkbox" id="publish-save" /><label for="publish-save"><span></span><?php _e("Save and move it under review", "social-articles"); ?></label>
            <?php endif?>
            <?php if(($status=="draft" || $status == "new-post") && $directWorkflow):?>
                <input type="checkbox" id="publish-save" /><label for="publish-save"><span></span><?php _e("Save and publish", "social-articles"); ?></label>
            <?php endif?>

            <input type="submit" class="button save" value="<?php _e("Save", "social-articles"); ?>" onclick="savePost(); return false;" />
            <input type="submit" class="button cancel" value="<?php _e("Cancel", "social-articles"); ?>" onclick="window.open('<?php echo $bp->loggedin_user->domain.'/articles';?>', '_self')" />
        </div>  
    </div>    
<?php else:?>
    <div id="message" class="messageBox note icon">
        <span><?php echo $message; ?></span>
    </div>    
<?php endif;?>

<script> 
jQuery(function(){                    
    new AjaxUpload('uploader', {
        action: MyAjax.baseUrl+'/upload-handler.php',                
                onComplete: function(file, response){                                       
                    response = jQuery.parseJSON(response);
                    jQuery("#uploading").hide();
                    if(response.status == "ok"){                                                           
                        jQuery("#image-name").val(response.value);
                        jQuery("#image-preview-container").html(getImageObject(MyAjax.tmpImageUrl+ response.value));
                        jQuery(".edit-controls").show();                                                    
                    }else{
                        jQuery(".upload-controls").show();   
                        showError(response.value);                                
                    }
                },
                onSubmit: function(file, extension){
                   jQuery('#error-box').hide();
                   jQuery(".upload-controls").hide();
                   jQuery("#uploading").show();                              
                }   
            });         
        
        });             

</script>
