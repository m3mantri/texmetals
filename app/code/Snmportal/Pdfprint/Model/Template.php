<?php
namespace Snmportal\Pdfprint\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;

class Template extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 0;

    const TYPE_ORDER=1;
    const TYPE_INVOICE=2;
    const TYPE_SHIPPING=3;
    const TYPE_CREDITMEMO=4;
    /**
     * Import source file.
     */
//    const FIELD_NAME_SOURCE_FILE = 'import_file';

    /**
     * @var \Magento\Framework\HTTP\Adapter\FileTransferFactory
     */
    protected $_httpFactory;

    /**
     * @var \Snmportal\Pdfprint\Logger\Logger
     */
    protected $_snmLogger;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_varDirectory;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_rootDirectory;

    /**
     * @var \Snmportal\Pdfprint\Model\Options\TypeHash
     */
    protected $typeOptions;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    protected $open_pdf_files = array();
    public function __construct(
        \Snmportal\Pdfprint\Logger\Logger $snmLogger,
        \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Snmportal\Pdfprint\Model\Options\TypeHash $typeOptions,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,

        array $data = []
    ) {
        $this->_snmLogger = $snmLogger;
        $this->typeOptions = $typeOptions;
        $this->_filesystem = $filesystem;
        $this->_httpFactory = $httpFactory;
        $this->_uploaderFactory = $uploaderFactory;
        $this->messageManager = $messageManager;


        $this->_varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->_rootDirectory= $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        parent::__construct($context, $registry, $resource,$resourceCollection,$data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Snmportal\Pdfprint\Model\ResourceModel\Template');
    }

    public function __destruct()
    {
        $directory = $this->_filesystem->getDirectoryWrite(DirectoryList::TMP);
        foreach ($this->open_pdf_files as $file) {
            $directory->delete($directory->getRelativePath($file));
        }
    }

    public function getFullPath($field)
    {
        $data = $this->getPDFFileData($field);
        if ($data) {
            if (isset($this->open_pdf_files[$field])) {
                return $this->open_pdf_files[$field];
            }

            if (isset($data['data'])) {
                $directory = $this->_filesystem->getDirectoryWrite(DirectoryList::TMP);
                $directory->create();
                $tmpFileName = $directory->getAbsolutePath(
                    'snm_pdfprint_' . uniqid(\Magento\Framework\Math\Random::getRandomNumber()) . time() . '.pdf'
                );
                file_put_contents($tmpFileName, $data['data']);
                $this->open_pdf_files[$field] = $tmpFileName;
//                $directory->delete($directory->getRelativePath($tmpFileName));
                return $tmpFileName;
            }
            /*
                        $sourceFile = $this->getWorkingDir() . $file;
                        if ( file_exists($sourceFile) )
                        {
                            return $sourceFile;
                        }
            */
        }
        return false;
    }

    public function getPdfFilename($field)
    {
        $data = $this->getPDFFileData($field);
        if ($data) {
            return $data['fname'];
        }
        return '';
//        $n = explode('-',$this->getData($field),3);
        //      return count($n) == 3 ?  $n[2]:'';
    }

    public function getPDFFileData($field)
    {
        $pdfFiles = $this->getData('pdf_files');
        if ($pdfFiles && is_array($pdfFiles)) {
            foreach ($pdfFiles as $pdfFile) {
                if (isset($pdfFile['field']) && $pdfFile['field'] == $field) {
                    return $pdfFile;
                }
            }
        }
        return false;
    }

    public function setPDFFileData($field, $fname, $data = false)
    {
        $pdfFiles = $this->getData('pdf_files');
        $this->setData($field, $fname);
        // Clear
        $pdfFilesTmp = [];
        if ($pdfFiles && is_array($pdfFiles)) {
            foreach ($pdfFiles as $pdfFile) {
                if ( is_array($pdfFile))
                    $pdfFilesTmp[]=$pdfFile;
            }
            $pdfFiles =$pdfFilesTmp;
        }

        if ($pdfFiles && is_array($pdfFiles)) {
            foreach ($pdfFiles as &$pdfFile) {
                if ($pdfFile['field'] == $field) {
                    $pdfFile['fname'] = $fname;
                    $pdfFile['data'] = $data;
                    $this->setData('pdf_files', $pdfFiles);
                    return true;
                }
            }
        }
        $pdfFiles[] = array(
            'field' => $field,
            'fname' => $fname,
            'data' => $data
        );
        $this->setData('pdf_files', $pdfFiles);
    }
    public function getAvailableTypes()
    {
        return $this->typeOptions->toHashArray();
    }

    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
    public function getOptionYesNo()
    {
        return [0 => __('No'), 1 => __('Yes')];
    }
    public function getOptionDisplayTaxTotal()
    {
        return [0 => __('No'), 1 => __('Yes'), 2 => __('Only at different of taxation.')];
    }

    public function getTypeName()
    {
        foreach ( $this->typeOptions->toOptionArray() as $option)
            if ( $option['value'] == $this->getType() )
                return $option['label'];
        return '';
    }
    public function isTemplate($type)
    {
        return $this->getType()  == $type;
    }
    public function getFreePrintItems($page)
    {

        return $page==1?$this->getData('free_items'):$this->getData('free_items_p2');
    }
    /*
    public function getAvailableTypes()
    {
        return [self::TYPE_ORDER => __('Order'),
                self::TYPE_INVOICE => __('Invoice'),
                self::TYPE_SHIPPING => __('Shipping'),
                self::TYPE_CREDITMEMO => __('Credit Memo'),
        ];
    }
*/
    /**
     * Check if page identifier exist for specific store
     * return template id if template exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */

    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    public function getWorkingDir()
    {
        return $this->_varDirectory->getAbsolutePath('tmp/');
        //return $this->_varDirectory->getAbsolutePath('snmportal/pdfprint/');
    }

    /**
     * @return \Snmportal\Pdfprint\Model\Template
     */
    public function isCompleteToCreate()
    {
        return $this->getType();
    }
    public function getAppendixPath()
    {
        if ( $this->getData('pdf_appendix_use') )
        {
            return $this->getFullPath('pdf_appendix');
        }
        return false;
    }

    public function getEmailAttachments()
    {
        $attachments =array();
        for ( $i=1; $i <= 2; $i++ )
        {
            if ( $this->getData('pdf_attachment'.$i.'_use') )
            {
                $attachments[]=array(
                    'path'=>$this->getFullPath('pdf_attachment'.$i),
                    'name'=>$this->getData('pdf_attachment'.$i.'_name')
                );
            }
        }
        return $attachments;
    }
    public function getAttachFilename()
    {
        return $this->getData('pdf_download_name');
    }

    public function uploadSource()
    {
        /** @var $adapter \Zend_File_Transfer_Adapter_Http */
        $adapter = $this->_httpFactory->create();

        foreach (array('pdf_background','pdf_appendix','pdf_attachment1','pdf_attachment2') as $fieldName )
        {
            //$this->_snmLogger->info('xxxx:'.$fieldName.'_delete'.' >:'.$this->getData($fieldName.'_delete'));
            if ( $this->getData($fieldName.'_delete') == 'on' ) {
                $this->setPDFFileData($fieldName, '',null);
                continue;
            }
            if (!$adapter->isUploaded('file_'.$fieldName)) continue;
            if (!$adapter->isValid('file_'.$fieldName)) {

                $messages = $adapter->getMessages();
                if ( is_array($messages) && !isset($messages['fileUploadErrorNoFile']) )
                {
                    foreach ( $messages as $message )
                    {
                        $this->messageManager->addError($message);
                    }
                }
                //$this->_snmLogger->info('no importfile:'.'file_'.$fieldName);
                continue;
            }

            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_uploaderFactory->create(['fileId' => 'file_'.$fieldName]);
            $uploader->skipDbProcessing(true);
            $result = $uploader->save($this->getWorkingDir());

            $extension = pathinfo($result['file'], PATHINFO_EXTENSION);
            //$uploadedFile = $result['path'] . $result['file'];
            $uploadedFile = 'tmp/' . $result['file'];
            $absuluteUploadedPath = $this->getWorkingDir() . $result['file'];

            if (!$extension) {
                $this->_varDirectory->delete($uploadedFile);
                throw new \Magento\Framework\Exception\LocalizedException(__('The pdf template you uploaded has no extension.'));
            }
            if ($extension != 'pdf') {
                //$this->_snmLogger->info('SaveINfo',$result);
                if ( !$this->_varDirectory->delete($uploadedFile) )
                    $this->_snmLogger->info('Cant delete :'.$uploadedFile);
                throw new \Magento\Framework\Exception\LocalizedException(__('The pdf template you uploaded has no extension ".pdf".'));
            }

            $this->setPDFFileData($fieldName, basename($uploadedFile), file_get_contents($absuluteUploadedPath));

            if (!$this->_varDirectory->delete($uploadedFile)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Can\'t delete temp file.'));
            }


            /*
            $uniqId = $this->getUniqTemplateId();
            if ( !$uniqId )
            {
                $uniqId = uniqid('snm');
                $this->setData('uniq_template_id',$uniqId);
            }

            $sourceFile = $this->getWorkingDir() . $uniqId.'-'.$fieldName.'-'.$result['file'];
            $sourceFileRelative = $this->_varDirectory->getRelativePath($sourceFile);

            if (strtolower($uploadedFile) != strtolower($sourceFile)) {
                if ($this->_varDirectory->isExist($sourceFileRelative)) {
                    $this->_varDirectory->delete($sourceFileRelative);
                }
                try {
                    $this->_varDirectory->renameFile(
                        $this->_varDirectory->getRelativePath($uploadedFile),
                        $sourceFileRelative
                    );
                } catch (\Magento\Framework\Exception\FileSystemException $e) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('The source file moving process failed.'));
                }
            }
            $this->setData($fieldName,basename($sourceFile));
            //$this->_snmLogger->info($sourceFile);
            */

        }
        return $this;
    }
    public function importFile($fieldName,$uploadedFile)
    {
        /*
        $uniqId = $this->getUniqTemplateId();
        if ( !$uniqId )
        {
            $uniqId = uniqid('snm');
            $this->setData('uniq_template_id',$uniqId);
        }

        $sourceFile = $this->getWorkingDir() . $uniqId.'-'.$fieldName.'-'.basename($uploadedFile);
        $sourceFileRelative = $this->_rootDirectory->getRelativePath($sourceFile);
        if (strtolower($uploadedFile) != strtolower($sourceFile)) {
            if ($this->_rootDirectory->isExist($sourceFileRelative)) {
                $this->_rootDirectory->delete($sourceFileRelative);
            }
            try {
                $fromFileRelative = $this->_rootDirectory->getRelativePath($uploadedFile);
                $this->_rootDirectory->copyFile(
                    $fromFileRelative,
                    $sourceFileRelative
                );
            } catch (\Magento\Framework\Exception\FileSystemException $e) {

                throw new \Magento\Framework\Exception\LocalizedException(__('The source file moving process failed.<br/>From %1<br/>To %2.',''.$fromFileRelative,''.$sourceFileRelative));
            }
        }
        $this->setData($fieldName,basename($sourceFile));
        //$this->_snmLogger->info($sourceFile);
        */
    }
    public function importFileString($fieldName,$fname,$content)
    {
        /*
        $uniqId = $this->getUniqTemplateId();
        if (!$uniqId) {
            $uniqId = uniqid('snm');
            $this->setData('uniq_template_id', $uniqId);
        }

        $sourceFile = $this->getWorkingDir() . $uniqId . '-' . $fieldName . '-' . $fname;

        $sourceFileRelative = $this->_varDirectory->getRelativePath($sourceFile);

        try {
            $this->_varDirectory->writeFile(
                $sourceFileRelative,
                $content
            );
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The source file moving process failed.'));
        }
        return basename($sourceFile);
        */
        //$this->setData($fieldName, basename($sourceFile));
    }
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            foreach ( $key as $k => $v)
                parent::setData($k, $v);
            return $this;
        }
        return parent::setData($key, $value);
    }
    public function getMargin($pangenr,$key)
    {
        $margins=array();
        if ( $pangenr > 1 )
        {
            $margins=$this->getData('margins2');
        }else
            $margins=$this->getData('margins1');
        if ( is_array($margins) && count($margins) > 0 )
            $margins=$margins[0];
        else
            $margins=array();
        if ( isset($margins[$key]) )
        {
            return floatval($margins[$key]);
        }
        return 10;
    }
    public function getBlockTemplate($blockId)
    {
        $templates=$this->getData('block_templates');
        if ( $templates && is_array($templates) )
        {
            foreach ( $templates as $template )
            {
                if ( trim($template['name']) == trim($blockId) )
                {
                    return $template;
                }
            }
        }
        return false;
    }
    public function export()
    {
        $data = $this->getData();
        $excludes = array('template_id', 'identifier', 'creation_time', 'update_time');//,'uniq_template_id'
        // $files = array('pdf_attachment1','pdf_attachment2','pdf_background','pdf_appendix');
        $export = array();
        foreach ( $data as $key => $value )
        {
            if ( !is_object($value) && !in_array($key,$excludes) )
            {
                $export[$key]=$value;
            }
        }
        return serialize($export);
    }
    public function import()
    {
        /** @var $adapter \Zend_File_Transfer_Adapter_Http */
        $adapter = $this->_httpFactory->create();

        foreach (array('pdf_import') as $fieldName )
        {
            if ( $this->getData($fieldName.'_delete') == 'on' )
            {
                $this->setData($fieldName,'');
                continue;
            }
            if (!$adapter->isUploaded('file_'.$fieldName)) continue;
            if (!$adapter->isValid('file_'.$fieldName)) {

                $messages = $adapter->getMessages();
                if ( is_array($messages) && !isset($messages['fileUploadErrorNoFile']) )
                {
                    foreach ( $messages as $message )
                    {
                        $this->messageManager->addError($message);
                    }
                }
                continue;
            }
            $fname = $adapter->getFileName('file_'.$fieldName);
            $content='';
            if ( $adapter->receive('file_'.$fieldName) )
            {
                $data = unserialize(file_get_contents($fname));
                if ( isset($data['store_id']) )
                    $data['store_id']=array();
                $this->setData($data);
            }
        }
        return $this;
    }
    public function translateValue($name,$type='label')
    {

        $transTable = $this->getData('translation_table');
        if ( is_array($transTable) )
        {
            foreach ( $transTable as $trans )
            {
                if ( $type== 'label' && ( !isset($trans['uselabel']) ||  !$trans['uselabel']) )
                    continue;
                if ( $type== 'value' && ( !isset($trans['usevalue']) ||  !$trans['usevalue']) )
                    continue;
                if ( isset($trans['isregex']) && $trans['isregex'] )
                    $namer = preg_replace($trans['regex'],$trans['value'],$name);
                else
                    $namer = str_replace($trans['regex'],$trans['value'],$name);
                if ( $namer != $name && isset($trans['stop']) && $trans['stop'])
                {
                    $name = $namer;
                    break;
                }
                $name = $namer;
            }
        }
        return $name;
    }

}