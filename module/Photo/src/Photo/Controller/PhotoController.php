<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Photo for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Photo\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Photo\Form\PhotoForm;
use Application\Model\UserPhoto;

class PhotoController extends AbstractActionController
{
    public function indexAction()
    {
        return array();
    }

    public function addAction()
    {
        $form = new PhotoForm();
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            // Make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
    
            $form->setData($post);
            
            if ($form->isValid()) 
            {
                $data = $form->getData();
                // Form is valid, save the form!
                if ($request->isXmlHttpRequest()) 
                {
                    // the file
                    $file = $data['image-file']['tmp_name'];
                    
                    $path_parts = pathinfo($file);
                    
                    $ext = $path_parts['extension'];
                    $filename = $path_parts['basename'];
                    
                    $photoMapper = $this->getServiceLocator()->get('PhotoMapper');
                    
                    $photos = $photoMapper->fetchAll(array('owner'=>$this->identity()->user_id));
                    
                    $photo = new UserPhoto();
                    
                    $photo->setFilename($filename);
                    $photo->setOwner($this->identity()->user_id);
                    $photo->setStatus('pending');
                    $photo->setType('main');
                    
                    if(count($photos))
                    {
                        $photo->setType('other');
                    }
                    
                    $photoMapper->save($photo);
                    
                    return new JsonModel(array(
                        'status'   => 'success',
                        'filename' => $filename,
                        'photo_id' => $photo->getId()
                    ));
                } 
                else 
                {
                    // Fallback for non-JS clients
                    return $this->redirect()->toRoute('account/profil');
                }
                
            } 
            else 
            {
                if ($request->isXmlHttpRequest()) 
                {
                     // Send back failure information via JSON
                    $errors = array();
                    foreach($form->getMessages() as $input)
                    {
                        foreach($input as $error)
                        {
                            $errors[] = $error;
                    
                        } 
                    }
                    
                     return new JsonModel(array(
                         'status'     => 'error',
                         'error'      => $errors,
                     ));
                }
            }
        }
    
        return new JsonModel(array('form' => $form));
    }
    
    public function deleteAction()
    {
        $mapper = $this->getServiceLocator()->get('PhotoMapper');
        
        $photo = $mapper->find($this->params('id'));
        
        if($photo != null && $photo->getOwner() == $this->identity()->user_id)
        {
            $mapper->delete($photo);
            
            $path = getcwd() . '/public/photos/';;
            
            if($photo->getStatus() == 'pending')
            {
                $path .= 'pendings/';
            }
            
            $path_parts = pathinfo($photo->getFilename());
            
            $ext = $path_parts['extension'];
            $filename = $path_parts['filename'];
            
            $sizes = array('orig','small','xsmall');     
            
            foreach($sizes as $size)
            {
                $file = $filename.'_'.$size.'.'.$ext;
                
                if(file_exists($path.$file))
                {
                    unlink($path.$file);
                }
            }
            
            unlink($path.$photo->getFilename());

            $this->flashMessenger()->setNamespace('success')->addMessage('La photo a bien été supprimée !');
        }
        else
        {
            $this->flashMessenger()->setNamespace('error')->addMessage('Cette photo n\'existe pas, impossible de la supprimer !');
        }
        
        return $this->redirect()->toRoute('account/profil');
    }
    
    public function cropAction()
    {
        if($this->getRequest()->isPost())
        {
            $mapper = $this->getServiceLocator()->get('PhotoMapper');
            
            $data = $this->request->getPost();
            
            $photo = $mapper->find($data['photoId']);
            
            if($photo->getOwner() == $this->identity()->user_id)
            {      
                $publicPath = './public/photos/pendings/';
                
                $file = $publicPath.$photo->getFilename();
                
                $path_parts = pathinfo($file);
                
                $ext = $path_parts['extension'];
                $filename = $path_parts['filename'];
                
                // the desired width of the image
                $width = 250;
                $height = 250;
                 
                list($width_orig, $height_orig) = getimagesize($file);
                
                // resample
                $image_p = imagecreatetruecolor($width_orig * $data['crop']['scale'], $height_orig * $data['crop']['scale']);
                $image = imagecreatefromjpeg($file);
                imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width_orig * $data['crop']['scale'], $height_orig * $data['crop']['scale'], $width_orig, $height_orig);
                
                // output
                imagejpeg($image_p, $publicPath.$filename.'_orig.'.$ext, 100);
                
                list($width_orig, $height_orig) = getimagesize($publicPath.$filename.'_orig.'.$ext);
                $image_p = imagecreatetruecolor($width, $height);
                $image = imagecreatefromjpeg($publicPath.$filename.'_orig.'.$ext);
                imagecopyresampled($image_p, $image, 0, 0, $data['crop']['x'] , $data['crop']['y'], $width_orig, $height_orig, $width_orig, $height_orig);
                
                // output
                imagejpeg($image_p, $file, 100);
                
                $img_r = imagecreatefromjpeg($file);
                
                //SMALL
                $small = imagecreatetruecolor(80, 80);
                imagecopyresampled($small,$img_r,0,0,0,0,80,80,250,250);
                imagejpeg($small,$publicPath.$path_parts['filename']."_small.".$path_parts["extension"],100);
                
                
                //XSMALL
                $dst_r = imagecreatetruecolor(60, 60);
                imagecopyresampled($dst_r,$img_r,0,0,0,0,60,60,250,250);
                imagejpeg($dst_r,$publicPath.$path_parts['filename']."_xsmall.".$path_parts["extension"],100);
                
                return new JsonModel(array(
                    'status'   => 'success'
                ));
            }
        }
    }

}
