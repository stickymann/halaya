<?php
class SITEPDF extends TCPDF {

	//Page header
	public function Header() {
		// Logo
		//$image_file = K_PATH_IMAGES.'logo_example.jpg';
		$image_file = 'media/pdftemplate/images/grllogo.96x48.png';
		$this->Image($image_file, 12, 12, 0, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

		$this->SetFont('helvetica', '', 10);
		$html = '<span style="font-size: 20pt; font-weight: bold;">GPS Rescue Ltd.</span><br>55 Maloney Street, Petit Bourg <br>Trinidad, W.I.<br>Tel: 675-8000, 222-5888, 685-4477';
		$this->writeHTMLCell(60, 15, 55, 13, $html, 0, 0, 0, true, 'L', true);
		$html = ' <br>Email: gpsrescue@gmail.com<br>Website: www.gpsrescuett.com<br>Facebook: www.facebook.com/gpsrescue<br>VAT Registration No.: 179127';
		$this->writeHTMLCell(0, 15, 130, 13, $html, 0, 1, 0, true, 'L', true);
		$html = '<hr style="border: black solid 0px;">';
		$this->writeHTMLCell(0, 20, 12, 35, $html, 0, 0, 0, true, 'L', true);
	}

	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-12);
		// Set font
		$this->SetFont('helvetica', 'I', 8);
		// Page number
		//$this->writeHTMLCell(0, 0, 0, 10, "<hr>", 0, 0, 0, true, 'L', true);
		//$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
		if ($this->pagegroups > 0) 
		{			
			$this->Cell(0, 10, 'Page '.$this->getPageNumGroupAlias().'/'.$this->getPageGroupAlias(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
		} 
		else 
		{		
			$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');	
		}
	}
}

class TESTPDF extends TCPDF {

	//Page header
	public function Header() {
		// Logo
		//$image_file = K_PATH_IMAGES.'logo_example.jpg';
		$image_file = 'media/pdftemplate/images/grllogo.96x48.png';
		$this->Image($image_file, 12, 12, 0, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

		$this->SetFont('helvetica', '', 10);
		$html = '<span style="font-size: 30pt; font-weight: bold;">BIG FAT FRACKING TEST</span>';
		$this->writeHTMLCell(0, 15, 55, 13, $html, 0, 1, 0, true, 'L', true);
		$html = '<hr style="border: black solid 0px;">';
		$this->writeHTMLCell(0, 20, 12, 32, $html, 0, 0, 0, true, 'L', true);
	}

	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('helvetica', 'I', 8);
		// Page number
		if (empty($this->pagegroups)) 
		{
			$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');	
		} 
		else 
		{
			$this->Cell(0, 10, 'Page '.$this->getPageNumGroupAlias().'/'.$this->getPageGroupAlias(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		}
	}
}
?>