<template>
    <div class="tab-pane active" id="partymeister-slides" role="tabpanel">
        <div class="container mt-3" style="overflow:scroll; position: absolute; top: 0; bottom: 0;">
            <div class="form-group">
                <label class="control-label">
                    {{ t('motor-backend.backend.categories.category') }}
                </label>
                <select class="form-control" name="category_id" v-model="category_id" @change="refreshFiles">
                    <option value="">{{ t('motor-backend.backend.categories.all_categories') }}</option>
                    <option v-for="(category, index) in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>
            </div>
          <div class="flex">
            <button type="button" class="btn btn-sm btn-primary flex-row-reverse" @click="next" v-if="pagination && pagination.last_page > 1 && pagination.current_page < pagination.last_page"> >> </button>
            <button type="button" class="btn btn-sm btn-primary flex-row" @click="previous" v-if="pagination && pagination.last_page > 1 && (pagination.current_page >= pagination.last_page || (pagination.current_page > 1 && pagination.current_page < pagination.last_page))"> &lt;&lt; </button>
          </div>
            <div class="clearfix mb-2"></div>
            <draggable
              v-model="files"
              :group="{ name: 'files', pull: 'clone', put: false }"
              :sort="false"
              drag-class="sortable-drag"
              ghost-class="sortable-ghost"
              item-key="_uid"
              @start="onStart"
              @end="onEnd"
            >
              <template #item="{ element }">
                <div>
                    <div class="card">
                        <img v-if="isImage(element)" class="card-img-top" :src="element.file_preview?.conversions.preview">
                        <div class="card-body" data-toggle="tooltip" data-placement="top" :title="element.description">
                            <p class="card-text">
                                {{ element.name }}<br>
                                <span v-if="element.file" class="badge badge-secondary badge-pill">{{ element.file.mime_type }}</span>
                            </p>
                        </div>
                    </div>
                </div>
              </template>
            </draggable>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps({
    previewImage: String
});

const emit = defineEmits(['mediapool:drag:start', 'mediapool:drag:end']);

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const routeFn = getCurrentInstance().appContext.config.globalProperties.route;

const files = ref([]);
const categories = ref([]);
const category_id = ref('');
const pagination = ref(false);

let uidCounter = 0;

function assignUids(items) {
    return items.map(item => {
        if (!item._uid) {
            item._uid = '__slide_' + (uidCounter++);
        }
        return item;
    });
}

function onStart(e) {
    emit('mediapool:drag:start', true);
}

function onEnd(e) {
    emit('mediapool:drag:end', true);
}

function refreshFiles() {
    axios.get(routeFn('ajax.slides.index') + '?sortable_field=created_at&sortable_direction=DESC&category_id=' + category_id.value).then((response) => {
        files.value = assignUids(response.data.data);
        pagination.value = response.data.meta;
    });
}

function next() {
    axios.get(routeFn('ajax.slides.index') + '?sortable_field=created_at&sortable_direction=DESC&category_id=' + category_id.value + '&page=' + (pagination.value.current_page + 1)).then((response) => {
        files.value = assignUids(response.data.data);
        pagination.value = response.data.meta;
    });
}

function previous() {
    axios.get(routeFn('ajax.slides.index') + '?sortable_field=created_at&sortable_direction=DESC&category_id=' + category_id.value + '&page=' + (pagination.value.current_page - 1)).then((response) => {
        files.value = assignUids(response.data.data);
        pagination.value = response.data.meta;
    });
}

function isImage(file) {
    if (!file.file_final) {
        return false;
    }
    if (file.file_final.mime_type === 'image/png' || file.file_final.mime_type === 'image/jpg' || file.file_final.mime_type === 'image/jpeg' || file.file_final.mime_type === 'video/x-m4v' || file.file_final.mime_type === 'video/mp4') {
        return true;
    }
    return false;
}

onMounted(() => {
    axios.get(routeFn('ajax.categories.index') + '?scope=slides').then((response) => {
        categories.value = response.data.data;
    });
    axios.get(routeFn('ajax.slides.index') + '?sortable_field=created_at&sortable_direction=DESC').then((response) => {
        files.value = assignUids(response.data.data);
        pagination.value = response.data.meta;
    });
});
</script>

<style lang="scss">
</style>
