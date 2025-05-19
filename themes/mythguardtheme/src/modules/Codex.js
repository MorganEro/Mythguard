import { singletonToast } from './Toast';

class Codex {
  constructor() {
    this.bindEvents();
    this.state = new Map(); // Store state for each codex entry
    this.countElement = document.querySelector('.codex-count');
    this.updateCodexCount();
  }

  bindEvents() {
    document.addEventListener('click', e => {
      const clickedElement = e.target.closest('.delete-codex');
      if (clickedElement) {
        this.handleDeleteCodex(clickedElement);
      }
    });

    document.addEventListener('click', e => {
      const clickedElement = e.target.closest('.edit-codex');
      if (clickedElement) {
        this.handleUpdateCodex(clickedElement);
      }
    });

    document.addEventListener('click', e => {
      const clickedElement = e.target.closest('.update-codex');
      if (clickedElement) {
        this.handleSubmitCodex(clickedElement);
      }
    });



    document.addEventListener('click', e => {
      const clickedElement = e.target.closest('.submit-codex');
      if (clickedElement) {
        this.handleAddCodex(clickedElement);
      }
    });


  }

  //UI Methods
  getCodexElements(clickedElement) {
    const codexItem = clickedElement.closest('li');
    return {
      codexItem,
      titleField: codexItem.querySelector('.codex-title-field'),
      bodyField: codexItem.querySelector('.codex-body-field'),
      updateButton: codexItem.querySelector('.update-codex'),
      editButton: codexItem.querySelector('.edit-codex'),
      codexId: codexItem.getAttribute('data-id'),
    };
  }

  //Codex Methods

  getCodexState(codexId) {
    if (!this.state.has(codexId)) {
      this.state.set(codexId, {
        isEditing: false,
        originalTitle: '',
        originalBody: '',
        currentTitle: '',
        currentBody: '',
      });
    }
    return this.state.get(codexId);
  }

  updateCodexCount() {
    if (this.countElement) {
      wp.apiFetch({
        path: '/wp/v2/codex?per_page=1',
      })
        .then(response => {
          if (Array.isArray(response) && response.length > 0) {
            const totalCount = response[0].userCodexCount;
            this.countElement.textContent = `${totalCount}/15`;
          } else {
            // If no posts found, make another call to get just the count
            wp.apiFetch({
              path: '/mythguard/v1/codex-count',
            }).then(countResponse => {
              this.countElement.textContent = `${countResponse.count}/15`;
            });
          }
        })
        .catch(error => {
          console.error('Error fetching count:', error);
        });
    }
  }

  updateCodexState(codexId, updates) {
    const currentState = this.getCodexState(codexId);
    this.state.set(codexId, { ...currentState, ...updates });
  }
  async handleDeleteCodex(clickedElement) {
    const { codexId, codexItem } = this.getCodexElements(clickedElement);

    const confirmed = await singletonToast.confirm(
      'Are you sure you want to delete this codex?'
    );

    if (confirmed) {
      try {
        await wp.apiFetch({
          path: `/mythguard/v1/codex/${codexId}`,
          method: 'DELETE',
        });

        codexItem.remove();
        singletonToast.show('Codex deleted successfully', 'success');
        this.updateCodexCount(); // Update count after deleting
      } catch (e) {
        if (e.code && !e.code.includes('rest_')) {
          singletonToast.show(
            e.message.includes('not found')
              ? 'This codex entry no longer exists.'
              : 'Error deleting codex. Please try again.',
            'error'
          );
        }
      } finally {
        this.isProcessingDelete = false; // Reset the flag when done
      }
    } else {
      this.isProcessingDelete = false; // Reset the flag when done
    }
  }

  handleUpdateCodex(clickedElement) {
    const { codexId } = this.getCodexElements(clickedElement);
    const state = this.getCodexState(codexId);

    if (state.isEditing) {
      this.makeNoteReadOnly(clickedElement, true); // true to revert changes
    } else {
      this.makeNoteEditable(clickedElement);
    }
  }

  makeNoteEditable(clickedElement) {
    const { titleField, bodyField, updateButton, codexId } =
      this.getCodexElements(clickedElement);

    // Save original state
    this.updateCodexState(codexId, {
      isEditing: true,
      originalTitle: titleField.value,
      originalBody: bodyField.value,
      currentTitle: titleField.value,
      currentBody: bodyField.value,
    });

    titleField.removeAttribute('readonly');
    bodyField.removeAttribute('readonly');

    titleField.classList.add('codex-active-field');
    bodyField.classList.add('codex-active-field');
    updateButton.classList.add('update-codex--visible');
    clickedElement.textContent = 'Cancel';
  }

  makeNoteReadOnly(clickedElement, revertChanges = false) {
    const { titleField, bodyField, updateButton, codexId } =
      this.getCodexElements(clickedElement);
    const state = this.getCodexState(codexId);

    if (revertChanges && state.isEditing) {
      titleField.value = state.originalTitle;
      bodyField.value = state.originalBody;
    }

    titleField.setAttribute('readonly', true);
    bodyField.setAttribute('readonly', true);

    titleField.classList.remove('codex-active-field');
    bodyField.classList.remove('codex-active-field');
    updateButton.classList.remove('update-codex--visible');
    clickedElement.textContent = 'Update';

    this.updateCodexState(codexId, { isEditing: false });
  }

  async handleSubmitCodex(clickedElement) {
    const { titleField, bodyField, codexId } =
      this.getCodexElements(clickedElement);
    const state = this.getCodexState(codexId);
    const updatedTitle = titleField.value;
    const updatedBody = bodyField.value;

    const hasChanges =
      updatedTitle !== state.originalTitle ||
      updatedBody !== state.originalBody;

    try {
      const response = await wp.apiFetch({
        path: `/mythguard/v1/codex/${codexId}`,
        method: 'PUT',
        data: {
          title: updatedTitle,
          body: updatedBody,
          status: 'publish',
        },
      });

      if (!response || response.error) {
        throw new Error(response?.error?.message || 'Failed to update codex');
      }

      const { editButton } = this.getCodexElements(clickedElement);
      this.makeNoteReadOnly(editButton);

      if (hasChanges) {
        singletonToast.show('Codex updated successfully', 'success');
      }
    } catch (e) {
      console.error('Update error:', e);
      singletonToast.show('Error updating codex. Please try again.', 'error');
    }
  }

  async handleAddCodex() {
    const title = document.querySelector('.new-codex-title').value;
    const body = document.querySelector('.new-codex-body').value;

    if (!title || !body) {
      singletonToast.show('Please fill in both fields', 'error');
      return;
    }

    try {
      const response = await wp.apiFetch({
        path: '/mythguard/v1/codex',
        method: 'POST',
        data: {
          title,
          body,
          status: 'publish',
        },
      });

      if (!response || response.error) {
        const errorMessage = response?.error?.message || 'Failed to add codex';
        singletonToast.show(errorMessage, 'error');
        return;
      }

      this.insertNewCodexItem(response);
      singletonToast.show('Codex added successfully', 'success');
      this.hideCreateForm();
      this.updateCodexCount(); // Update count after adding
    } catch (e) {
      console.error('Add error:', e);
      // Check if it's the 403 limit error and has the error message
      if (e.data && e.data.message) {
        singletonToast.show(e.data.message, 'error');
      } else {
        singletonToast.show('Error adding codex. Please try again.', 'error');
      }
    }
  }

  clearNewCodexFields() {
    document.querySelector('.new-codex-title').value = '';
    document.querySelector('.new-codex-body').value = '';
  }

  showCreateForm() {
    const form = document.querySelector('.create-codex-form');
    const addButton = document.querySelector('.add-codex');
    form.classList.add('is-visible');
    addButton.classList.add('is-hidden');
  }

  hideCreateForm() {
    const form = document.querySelector('.create-codex-form');
    const addButton = document.querySelector('.add-codex');
    form.classList.remove('is-visible');
    addButton.classList.remove('is-hidden');
    this.clearNewCodexFields();
  }

  insertNewCodexItem(codex) {
    const codexList = document.getElementById('codex');
    const newCodexHtml = `
            <li class="codex-item" data-id="${codex.id}">
                <div class="codex-title-wrapper">
                    <input class="codex-title-field" value="${codex.title}" readonly>
                    <div class="codex-actions">
                        <button class="edit-codex btn btn--small btn--yellow">Update</button>
                        <button class="delete-codex btn btn--small btn--dark-orange">Delete</button>
                    </div>
                </div>
                <textarea class="codex-body-field" readonly>${codex.body}</textarea>
                <button class="update-codex btn btn--small btn--blue">Save</button>
            </li>
        `;
    codexList.insertAdjacentHTML('afterbegin', newCodexHtml);
  }
}

export default Codex;
