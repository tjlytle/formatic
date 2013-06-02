<?php
namespace OpenForm\Pdf;

class PdfDocument extends \ZendPdf\PdfDocument
{
    /**
     * List of form fields
     *
     * @var array - Associative array, key: name of form field, value: Zend_Pdf_Element
     */
    protected $_formFields = array();    
    
    public function __construct($source = null, $revision = null, $load = false)
    {
        return parent::__construct($source, $revision, $load);
        
var_dump($source);        
        
        if($source !== null){
            $this->_loadFormFields($this->_trailer->Root);
        }
        
        var_dump($this->_formFields);
        
    }
    
    /**
     * Load form fields
     * Populates the _formFields array, for later lookup of fields by name
     *
     * @param Zend_Pdf_Element_Reference $root Document catalog entry
     */
    protected function _loadFormFields(Zend_Pdf_Element_Reference $root)
    {

var_dump($root->AcroForm);
exit;
        if ($root->AcroForm === null || $root->AcroForm->Fields === null) {
            return;
        }
        
        foreach ($root->AcroForm->Fields->items as $field)
        {
            if ( $field->FT->value == 'Tx' && $field->T !== null ) /* We only support fields that are textfields and have a name */
            {
                $this->_formFields[$field->T->value] = $field;
            }
        }
        
        if ( !$root->AcroForm->NeedAppearances || !$root->AcroForm->NeedAppearances->value )
        {
            /* Ask the .pdf viewer to generate its own appearance data, so we do not have to */
            $root->AcroForm->add(new Zend_Pdf_Element_Name('NeedAppearances'), new Zend_Pdf_Element_Boolean(true) );
            $root->AcroForm->touch();
        }
    }
}