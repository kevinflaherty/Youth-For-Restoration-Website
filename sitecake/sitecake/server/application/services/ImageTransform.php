<?php

include_once "WideImage/WideImage.php";

class ImageTransform
{
	var $image;
 
	public function load( $filename ) {
		$this->image = WideImage::load($filename);
	}
   
	public function save( $filename, $asType=null, $compression=null )
	{
		$this->image->saveToFile($filename);
	}
   
	public function getWidth()
	{
		return $this->image->getWidth();
	}
   
	public function getHeight()
	{
		return $this->image->getHeight();
	}
   
	public function resizeToHeight( $height )
	{
		$this->image = $this->image->resize( null, $height );
	}
   
	public function resizeToWidth( $width )
	{
		$this->image = $this->image->resize( $width,null );
	}

	public function resizeToDimension($dimension)
	{
		if ( $this->image->getWidth() >= $this->image->getHeight() )
		{
			$this->resizeToWidth( $dimension );
		}
		else
		{
			$this->resizeToHeight( $dimension );
		}
	}
   
	public function scale( $scale )
	{
		$width = $this->getWidth() * $scale/100;
		$height = $this->getHeight() * $scale/100; 
		$this->image = $this->image->resize( $width, $height );
	}
   
	public function resize( $width, $height )
	{
		$this->image = $this->image->resize( $width, $height );
	}
   
	public function transform( $sx, $sy, $swidth, $sheight, $dwidth, $dheight )
	{
		if ( $dwidth == null )
		{
			$dwidth = $this->getWidth();
		}
		
		if ( $dheight == null )
		{
			$dheight = $this->getHeight();
		}

		$this->image = $this->image->crop($sx, $sy, $swidth, $sheight)->resize($dwidth, $dheight);
	}
}

?>