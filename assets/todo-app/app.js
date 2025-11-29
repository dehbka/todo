import { createApp, ref, reactive, computed, onMounted, nextTick } from 'vue';
import { createVuetify } from 'vuetify';
// Register Vuetify components & directives when using ESM + ImportMap
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { listTodos, createTodo, updateTodo, listComments, createComment } from '../api/todos.js';

// Vue + Vuetify implementation of the Todo app
function createTodoStore() {
  const items = ref([]);
  const loading = ref(false);
  const error = ref(null);

  async function refresh() {
    loading.value = true;
    error.value = null;
    try {
      items.value = await listTodos();
    } catch (e) {
      error.value = e.body?.message || e.message || 'Failed to load';
    } finally {
      loading.value = false;
    }
  }

  function addItem(todo) {
    items.value = [todo, ...items.value];
  }

  function replaceItem(updated) {
    items.value = items.value.map((t) => (t.id === updated.id ? updated : t));
  }

  return { items, loading, error, refresh, addItem, replaceItem };
}

const App = {
  setup() {
    const store = createTodoStore();
    const form = reactive({ title: '' });
    const formSubmitting = ref(false);
    const formError = ref(null);

    const editingId = ref(null);
    const editingTitle = ref('');
    const saving = ref(false);
    const itemError = ref(null);

    // Inline "new todo" card state
    const newItemActive = ref(false);
    const newTitle = ref('');
    const creatingNew = ref(false);
    const newItemError = ref(null);
    const newInputRef = ref(null);

    onMounted(() => store.refresh());

    // Template helpers to avoid passing Refs directly to Vuetify props
    const loadingBool = computed(() => !!store.loading.value);
    const savingBool = computed(() => !!saving.value);
    const creatingNewBool = computed(() => !!creatingNew.value);
    const safeItems = computed(() => Array.isArray(store.items.value) ? store.items.value.filter(Boolean) : []);

    async function submitCreate() {
      if (!form.title.trim()) {
        formError.value = 'Title is required';
        return;
      }
      formSubmitting.value = true;
      formError.value = null;
      try {
        const todo = await createTodo(form.title.trim());
        form.title = '';
        store.addItem(todo);
      } catch (e) {
        const problem = e.body;
        formError.value = problem?.message || problem?.detail || 'Failed to create';
      } finally {
        formSubmitting.value = false;
      }
    }

    function startEdit(todo) {
      editingId.value = todo.id;
      editingTitle.value = todo.title;
      itemError.value = null;
    }

    function cancelEdit(todo) {
      editingId.value = null;
      editingTitle.value = '';
      itemError.value = null;
    }

    async function saveTitle(todo) {
      const trimmed = editingTitle.value.trim();
      if (!trimmed) {
        itemError.value = 'Title cannot be empty';
        return;
      }
      saving.value = true;
      itemError.value = null;
      try {
        const updated = await updateTodo(todo.id, { title: trimmed });
        store.replaceItem(updated);
        editingId.value = null;
      } catch (e) {
        const problem = e.body;
        itemError.value = problem?.message || problem?.detail || 'Failed to update';
      } finally {
        saving.value = false;
      }
    }

    async function toggleStatus(todo) {
      const next = todo.status === 'done' ? 'open' : 'done';
      saving.value = true;
      itemError.value = null;
      try {
        const updated = await updateTodo(todo.id, { status: next });
        store.replaceItem(updated);
      } catch (e) {
        itemError.value = e.body?.message || 'Failed to update';
      } finally {
        saving.value = false;
      }
    }

    function startNewItem() {
      if (newItemActive.value) return;
      newItemActive.value = true;
      newTitle.value = '';
      newItemError.value = null;
      nextTick(() => {
        if (newInputRef.value?.focus) newInputRef.value.focus();
      });
    }

    function cancelNewItem() {
      newItemActive.value = false;
      newTitle.value = '';
      newItemError.value = null;
    }

    async function saveNewItem() {
      const title = newTitle.value.trim();
      if (!title) {
        newItemError.value = 'Title is required';
        return;
      }
      creatingNew.value = true;
      newItemError.value = null;
      try {
        const todo = await createTodo(title);
        store.addItem(todo);
        cancelNewItem();
      } catch (e) {
        const problem = e.body;
        newItemError.value = problem?.message || problem?.detail || 'Failed to create';
      } finally {
        creatingNew.value = false;
      }
    }

    // Comments / details dialog state & actions
    const detailsOpen = ref(false);
    const detailsTodo = ref(null);
    const comments = ref([]);
    const commentsLoading = ref(false);
    const commentsError = ref(null);
    const newComment = ref('');
    const addingComment = ref(false);

    const isDetailsTodoOpen = computed(() => detailsTodo.value?.status === 'open');

    async function openComments(todo) {
      detailsTodo.value = todo;
      detailsOpen.value = true;
      commentsLoading.value = true;
      commentsError.value = null;
      try {
        comments.value = await listComments(todo.id);
      } catch (e) {
        commentsError.value = e.body?.message || e.message || 'Failed to load comments';
      } finally {
        commentsLoading.value = false;
      }
    }

    function closeComments() {
      detailsOpen.value = false;
      detailsTodo.value = null;
      comments.value = [];
      newComment.value = '';
      commentsError.value = null;
    }

    async function saveComment() {
      const msg = newComment.value.trim();
      if (!detailsTodo.value) return;
      if (!msg) {
        commentsError.value = 'Message is required';
        return;
      }
      addingComment.value = true;
      commentsError.value = null;
      try {
        const c = await createComment(detailsTodo.value.id, msg);
        // Prepend new comment
        comments.value = [c, ...comments.value];
        newComment.value = '';
      } catch (e) {
        const problem = e.body;
        commentsError.value = problem?.message || problem?.detail || 'Failed to add comment';
      } finally {
        addingComment.value = false;
      }
    }

    return {
      store,
      safeItems,
      form,
      formSubmitting,
      formError,
      editingId,
      editingTitle,
      saving,
      savingBool,
      itemError,
      submitCreate,
      startEdit,
      cancelEdit,
      saveTitle,
      toggleStatus,
      // new item
      newItemActive,
      newTitle,
      creatingNew,
      creatingNewBool,
      newItemError,
      newInputRef,
      startNewItem,
      cancelNewItem,
      saveNewItem,
      loadingBool,
      // comments dialog
      detailsOpen,
      detailsTodo,
      comments,
      commentsLoading,
      commentsError,
      newComment,
      addingComment,
      openComments,
      closeComments,
      saveComment,
      isDetailsTodoOpen,
    };
  },
  template: `
    <v-app>
      <v-main>
        <v-container max-width="720">
          <h1 class="text-h4 my-4">Todos</h1>
          <div class="d-flex align-center ga-2 mb-4">
            <v-btn @click="startNewItem" :disabled="newItemActive || creatingNewBool" color="primary">New</v-btn>
            <v-btn @click="store.refresh" :loading="loadingBool" variant="tonal">Refresh</v-btn>
            <span v-if="store.loading">Loading…</span>
            <span v-if="store.error" class="text-error">{{ store.error }}</span>
          </div>

          <div v-if="!store.items.length && !store.loading" class="text-medium-emphasis">No todos yet. Create one!</div>

          <v-list v-else lines="one">
            <!-- Inline new item card -->
            <v-list-item v-if="newItemActive" :ripple="false" style="opacity: 0.6;">
              <template #prepend>
                <v-icon class="mr-2" color="grey">mdi-plus-circle-outline</v-icon>
              </template>
              <div class="d-flex ga-2 align-center w-100">
                <v-text-field
                  ref="newInputRef"
                  v-model="newTitle"
                  :disabled="creatingNew"
                  placeholder="New todo title…"
                  hide-details
                  density="compact"
                  class="flex-1-1"
                  @keydown.enter.prevent="saveNewItem"
                />
                <v-btn @click="saveNewItem" :loading="creatingNew" color="primary" variant="elevated">Save</v-btn>
                <v-btn @click="cancelNewItem" :disabled="creatingNew" variant="text">Cancel</v-btn>
              </div>
            </v-list-item>

            <v-list-item v-for="(t, i) in safeItems" :key="t && t.id ? t.id : i">
              <template #prepend>
                <v-btn icon variant="text" :loading="savingBool" @click="toggleStatus(t)" :title="'Toggle status'">
                  <v-icon>{{ t.status === 'done' ? 'mdi-check-circle' : 'mdi-checkbox-blank-circle-outline' }}</v-icon>
                </v-btn>
              </template>

              <template #default>
                <div v-if="editingId === t.id" class="d-flex ga-2 align-center w-100">
                  <v-text-field v-model="editingTitle" :disabled="savingBool" hide-details density="compact" class="flex-1-1" />
                  <v-btn @click="saveTitle(t)" :loading="savingBool" color="primary" variant="elevated">Save</v-btn>
                  <v-btn @click="cancelEdit(t)" :disabled="savingBool" variant="text">Cancel</v-btn>
                </div>
                <div v-else class="d-flex align-center justify-space-between w-100">
                  <span :style="{ textDecoration: t.status === 'done' ? 'line-through' : 'none' }">{{ t.title }}</span>
                  <div class="d-flex ga-1">
                    <v-btn size="small" variant="text" @click="startEdit(t)">Edit</v-btn>
                    <v-btn size="small" variant="text" @click="openComments(t)">Comments</v-btn>
                  </div>
                </div>
              </template>

            </v-list-item>
          </v-list>

          <div v-if="newItemError" class="text-error mt-2">{{ newItemError }}</div>
          <div v-if="itemError" class="text-error mt-2">{{ itemError }}</div>
        </v-container>
      </v-main>

      <!-- Details / Comments Dialog -->
      <v-dialog v-model="detailsOpen" max-width="640">
        <v-card>
          <v-card-title class="d-flex align-center justify-space-between">
            <span>{{ detailsTodo ? detailsTodo.title : '' }}</span>
            <v-chip :color="detailsTodo && detailsTodo.status === 'done' ? 'success' : 'primary'" size="small">
              {{ detailsTodo ? detailsTodo.status : '' }}
            </v-chip>
          </v-card-title>
          <v-divider></v-divider>
          <v-card-text>
            <div v-if="commentsLoading">Loading comments…</div>
            <div v-else>
              <v-alert v-if="commentsError" type="error" variant="tonal" class="mb-3">{{ commentsError }}</v-alert>
              <v-list v-if="comments.length" density="compact">
                <v-list-item v-for="c in comments" :key="c.id">
                  <div class="d-flex align-center justify-space-between w-100">
                    <div>
                      <div class="text-body-2">{{ c.message }}</div>
                      <div class="text-caption text-medium-emphasis">{{ c.createdAt }}</div>
                    </div>
                  </div>
                </v-list-item>
              </v-list>
              <div v-else class="text-medium-emphasis">No comments yet.</div>

              <div class="mt-4">
                <v-text-field
                  v-model="newComment"
                  :disabled="addingComment || !isDetailsTodoOpen"
                  label="Add a comment"
                  placeholder="Type a comment…"
                  hide-details
                  density="comfortable"
                  @keydown.enter.prevent="isDetailsTodoOpen && saveComment()"
                />
                <div class="d-flex ga-2 mt-2">
                  <v-btn color="primary" :disabled="!isDetailsTodoOpen" :loading="addingComment" @click="saveComment">Save Comment</v-btn>
                  <v-btn variant="text" @click="closeComments">Close</v-btn>
                </div>
                <div v-if="!isDetailsTodoOpen" class="text-medium-emphasis mt-2">Comments are disabled for completed todos.</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-dialog>
    </v-app>
  `,
};

function mount() {
  const el = document.getElementById('todo-root');
  if (!el) return;
  const vuetify = createVuetify({ components, directives });
  console.log('[todo-app] mounting vue app…');
  createApp(App).use(vuetify).mount(el);
  console.log('[todo-app] mounted');
}

mount();
