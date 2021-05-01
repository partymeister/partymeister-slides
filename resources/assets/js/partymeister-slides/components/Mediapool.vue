<template>
    <div class="tab-pane active" id="partymeister-slides" role="tabpanel">
        <div class="container mt-3" style="overflow:scroll; position: absolute; top: 0; bottom: 0;">
            <div class="form-group">
                <label class="control-label">
                    {{ $t('motor-backend.backend.categories.category') }}
                </label>
                <select class="form-control" name="category_id" v-model="category_id" @change="refreshFiles">
                    <option value="">{{ $t('motor-backend.backend.categories.all_categories') }}</option>
                    <option v-for="(category, index) in categories" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>
            </div>
          <div class="flex">
            <button type="button" class="btn btn-sm btn-primary flex-row-reverse" @click="next" v-if="pagination && pagination.last_page > 1 && pagination.current_page < pagination.last_page"> >> </button>
            <button type="button" class="btn btn-sm btn-primary flex-row" @click="previous" v-if="pagination && pagination.last_page > 1 && (pagination.current_page >= pagination.last_page || (pagination.current_page > 1 && pagination.current_page < pagination.last_page))"> << </button>
          </div>
            <div class="clearfix mb-2"></div>
            <draggable v-model="files" :options="{group:{ name:'files',  pull:'clone', put:false }, sort: false, dragClass: 'sortable-drag', ghostClass: 'sortable-ghost'}" @start="onStart" @end="onEnd">
                <div v-for="file in files">
                    <div class="card">
<!--                        <img v-if="file.file.is_generating" class="card-img-top" :src="previewImage">-->
<!--                        <img v-if="!file.file.is_generating && isImage(file)" class="card-img-top" :src="file.file.preview">-->
                        <img v-if="isImage(file)" class="card-img-top" :src="file.file.preview">
                        <div class="card-body" data-toggle="tooltip" data-placement="top" :title="file.description">
                            <p class="card-text">
                                {{ file.name }}<br>
                                <span v-if="file.file" class="badge badge-secondary badge-pill">{{ file.file.mime_type }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </draggable>
        </div>
    </div>
</template>

<script>
    import draggable from 'vuedraggable';

    export default {
        name: 'partymeister-slides-mediapool',
        props: ['previewImage'],
        data: function() {
            return {
                files: [],
                categories: [],
                category_id: '',
                pagination: false
            };
        },
        components: {
            draggable,
        },
        methods: {
            onStart: function(e) {
                this.$emit('mediapool:drag:start', true);
            },
            onEnd: function(e) {
                this.$emit('mediapool:drag:end', true);
            },
            refreshFiles: function() {
                axios.get(route('ajax.slides.index')+'?sortable_field=created_at&sortable_direction=DESC&category_id='+this.category_id).then((response) => {
                    this.files = response.data.data;
                    this.pagination = response.data.meta;
                });
            },
            next: function() {
                axios.get(route('ajax.slides.index')+'?sortable_field=created_at&sortable_direction=DESC&category_id='+this.category_id+'&page='+(this.pagination.current_page+1)).then((response) => {
                    this.files = response.data.data;
                    this.pagination = response.data.meta;
                });
            },
            previous: function() {
                axios.get(route('ajax.slides.index')+'?sortable_field=created_at&sortable_direction=DESC&category_id='+this.category_id+'&page='+(this.pagination.current_page-1)).then((response) => {
                    this.files = response.data.data;
                    this.pagination = response.data.meta;
                });
            },
          isImage: function (file) {
            let data = this.getFileType(file);
            if (data.mime_type === 'image/png' || data.mime_type === 'image/jpg' || data.mime_type === 'image/jpeg' || data.mime_type === 'video/mp4') {
              return true;
            }
            return false;
          },
          getFileType(file) {
            let data = { file_name: 'unknown'};
            if (file.slide !== undefined && file.slide.file_final !== null) {
              data = file.slide.file_final;
            } else if (file.file_association !== undefined) {
              data = file.file_association
            }
            return data;
          }
        },
        mounted: function () {

            axios.get(route('ajax.categories.index')+'?scope=slides').then((response) => {
                this.categories = response.data.data;
            });
            axios.get(route('ajax.slides.index')+'?sortable_field=created_at&sortable_direction=DESC').then((response) => {
                this.files = response.data.data;
                this.pagination = response.data.meta;
            });
        }
    }
</script>
<style lang="scss">
</style>
