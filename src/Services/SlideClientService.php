<?php

namespace Partymeister\Slides\Services;

use Motor\Backend\Services\BaseService;
use Motor\Media\Models\FileAssociation;
use Partymeister\Slides\Models\SlideClient;

/**
 * Class SlideClientService
 */
class SlideClientService extends BaseService
{
    /**
     * @var string
     */
    protected $model = SlideClient::class;

    public function afterCreate()
    {
        $this->assignJingles();
    }

    public function afterUpdate()
    {
        $this->assignJingles();
    }

    protected function assignJingles()
    {
        $this->record->file_associations()
                     ->delete();
        $this->addFileAssociation('jingle_1');
        $this->addFileAssociation('jingle_2');
        $this->addFileAssociation('jingle_3');
        $this->addFileAssociation('jingle_4');
    }

    /**
     * @param $field
     */
    protected function addFileAssociation($field)
    {
        if ($this->request->get($field) == '' || $this->request->get($field) == 'deleted') {
            return;
        }

        $file = json_decode($this->request->get($field));

        // Create file association
        $fa = new FileAssociation();
        $fa->file_id = $file->id;
        $fa->model_type = get_class($this->record);
        $fa->model_id = $this->record->id;
        $fa->identifier = $field;
        $fa->save();
    }
}
