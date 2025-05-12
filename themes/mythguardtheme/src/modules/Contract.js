import { singletonToast } from './Toast';

class Contract {
  constructor() {
    this.isProcessing = false;
    this.form = document.querySelector('.create-contract-form');
    this.countElement = document.querySelector('.contract-count');
    this.guardianSelect = document.querySelector('.new-contract-guardian');
    this.programSelect = document.querySelector('.new-contract-program');
    this.formGroups = document.querySelectorAll('.form-group');
    this.revealButtons = document.querySelectorAll('.reveal-input-btn');
    this.formInputs = document.querySelectorAll('.form-group.is-hidden input, .form-group.is-hidden textarea, .form-group.is-hidden select');

    this.state = new Map();
    this.guardians = [];
    this.programs = [];

    this.bindEvents();
    this.updateContractCount();
    this.loadGuardians();
    this.loadPrograms();
    this.setupSelectListeners();
    this.setupRevealFields();
  }

  setupSelectListeners() {
    if (this.guardianSelect) {
      this.guardianSelect.addEventListener('change', () =>
        this.handleGuardianChange()
      );
    }
    if (this.programSelect) {
      this.programSelect.addEventListener('change', () =>
        this.handleProgramChange()
      );
    }
  }

  setupRevealFields() {
    // Add click handlers to reveal buttons
    this.revealButtons.forEach(button => {
      // Make sure button is clickable
      button.style.cursor = 'pointer';

      const handleClick = e => {
        const group = button.closest('.form-group');
        if (!group) {
          console.error('No form group found for button:', button.textContent);
          return;
        }

        group.classList.add('active');
        group.classList.remove('is-hidden');

        const input = group.querySelector('input, textarea, select');
        if (input) {
          input.focus();
        } else {
          console.error('No input found in form group');
        }
      };

      // Add the click handler
      button.addEventListener('click', handleClick);
    });

    // Keep fields visible if they have content
    this.formInputs.forEach(input => {
      const handleInput = () => {
        if (input.value) {
          const group = input.closest('.form-group');
          if (group) {
            group.classList.add('active');
            group.classList.remove('is-hidden');
          }
        }
      };

      // Add the input handlers
      input.addEventListener('input', handleInput);
      input.addEventListener('change', handleInput);

      // Check initial value
      if (input.value) {
        handleInput();
      }
    });
  }

  handleGuardianChange() {
    const selectedGuardianId = this.guardianSelect.value;

    if (!selectedGuardianId) {
      // If no guardian selected, show all programs
      this.updateProgramOptions(this.programs);
      return;
    }

    // Find the selected guardian and their related programs
    const guardian = this.guardians.find(
      g => g.id.toString() === selectedGuardianId
    );
    const relatedPrograms = guardian?.acf?.related_programs || [];

    if (relatedPrograms.length > 0) {
      // Get program IDs, handling both object and primitive cases
      const relatedProgramIds = relatedPrograms.map(program =>
        typeof program === 'object' ? program.ID : program
      );

      const filteredPrograms = this.programs.filter(program =>
        relatedProgramIds.includes(program.id)
      );
      this.updateProgramOptions(filteredPrograms);
    } else {
      this.updateProgramOptions([]);
    }
  }

  handleProgramChange() {
    const selectedProgramId = this.programSelect.value;

    if (!selectedProgramId) {
      // If no program selected, show all guardians
      this.updateGuardianOptions(this.guardians);
      return;
    }

    // Find guardians that have this program in their related_programs field
    const relatedGuardians = this.guardians.filter(guardian => {
      const relatedPrograms = guardian.acf?.related_programs || [];
      // Check if any of the related programs match the selected program ID
      return relatedPrograms.some(program => {
        // Handle both cases: program could be an ID or an object with an ID
        const programId = typeof program === 'object' ? program.ID : program;
        return programId === parseInt(selectedProgramId);
      });
    });
    this.updateGuardianOptions(relatedGuardians);
  }

  updateProgramOptions(programs) {
    if (!this.programSelect) return;

    const currentValue = this.programSelect.value;
    this.programSelect.innerHTML = '<option value="">Select Program</option>';

    const sortedPrograms = [...programs].sort((a, b) =>
      a.title.rendered.localeCompare(b.title.rendered)
    );

    sortedPrograms.forEach(program => {
      this.programSelect.innerHTML += `<option value="${program.id}" ${
        currentValue === program.id.toString() ? 'selected' : ''
      }>${program.title.rendered}</option>`;
    });
  }

  updateGuardianOptions(guardians) {
    if (!this.guardianSelect) return;

    const currentValue = this.guardianSelect.value;
    this.guardianSelect.innerHTML = '<option value="">Select Guardian</option>';

    const sortedGuardians = [...guardians].sort((a, b) =>
      a.title.rendered.localeCompare(b.title.rendered)
    );

    sortedGuardians.forEach(guardian => {
      this.guardianSelect.innerHTML += `<option value="${guardian.id}" ${
        currentValue === guardian.id.toString() ? 'selected' : ''
      }>${guardian.title.rendered}</option>`;
    });
  }

  bindEvents() {
    document.addEventListener('click', e => {
      const clickedElement = e.target.closest('[data-action]');
      if (!clickedElement) return;

      e.preventDefault();
      const action = clickedElement.dataset.action;

      switch (action) {
        case 'submit-contract':
          this.handleAddContract(clickedElement);
          break;
        case 'cancel-contract':
          this.resetForm();
          break;
        case 'edit-contract':
          this.handleEditContract(clickedElement);
          break;

        case 'update-contract':
          this.handleUpdateContract(clickedElement);
          break;
        case 'delete-contract':
          this.handleDeleteContract(clickedElement);
          break;
      }
    });
  }

  // UI Methods
  getContractElements(clickedElement) {
    const contractItem = clickedElement.closest('li') || clickedElement.closest('.single-contract-item');
    
    if (!contractItem) return null;
    return {
      contractItem,
      titleField: contractItem.querySelector('.contract-title-field, .single-contract-title-field'),
      descriptionField: contractItem.querySelector(
        '.contract-description-field, .single-contract-description-field'
      ),
      programField: contractItem.querySelector('.contract-program-field, .single-contract-program-field'),
      guardianField: contractItem.querySelector('.contract-guardian-field, .single-contract-guardian-field'),
      startDateField: contractItem.querySelector('.contract-start-field, .single-contract-start-field'),
      endDateField: contractItem.querySelector('.contract-end-field'),

      updateButton: contractItem.querySelector(
        '[data-action="update-contract"]'
      ),
      editButton: contractItem.querySelector('[data-action="edit-contract"]'),
      contractId: contractItem.dataset.id,
    };
  }

  getContractState(contractId) {
    if (!this.state.has(contractId)) {
      this.state.set(contractId, {
        isEditing: false,
        originalTitle: '',
        originalDescription: '',
        originalProgram: '',
        originalGuardian: '',
        originalStartDate: '',
        originalEndDate: '',
        currentTitle: '',
        currentDescription: '',
        currentProgram: '',
        currentGuardian: '',
        currentStartDate: '',
        currentEndDate: '',
      });
    }
    return this.state.get(contractId);
  }

  async loadGuardians() {
    const response = await wp.apiFetch({
      path: '/wp/v2/guardian?per_page=100',
    });
    this.guardians = response;
    this.updateGuardianOptions(response);
  }

  async loadPrograms() {
    const response = await wp.apiFetch({
      path: '/wp/v2/program?per_page=100',
    });
    this.programs = response;
    this.updateProgramOptions(response);
  }

  // State Management Methods
  toggleEditMode(contractId) {
    const state = this.getContractState(contractId);
    const elements = this.getContractElements(
      document.querySelector(`[data-id="${contractId}"]`)
    );

    if (!state.isEditing) {
      // Save current values before editing
      state.originalTitle = elements.titleField.value;
      state.originalDescription = elements.descriptionField.value;
      state.originalProgram = elements.programField.value;
      state.originalGuardian = elements.guardianField.value;
      state.originalStartDate = elements.startDateField.value;
      state.originalEndDate = elements.endDateField.value;
      state.originalNotes = elements.notesField?.value || '';

      // Enable editing
      elements.titleField.removeAttribute('readonly');
      elements.descriptionField.removeAttribute('readonly');
      elements.programField.removeAttribute('disabled');
      elements.guardianField.removeAttribute('disabled');
      elements.startDateField.removeAttribute('readonly');
      elements.endDateField.removeAttribute('readonly');


      if (elements.updateButton) elements.updateButton.style.display = 'inline-block';
      if (elements.editButton) {
        elements.editButton.textContent = 'Cancel';
        elements.editButton.classList.remove('btn--blue');
        elements.editButton.classList.add('btn--orange');
      }
    } else {
      // Restore original values
      elements.titleField.value = state.originalTitle;
      elements.descriptionField.value = state.originalDescription;
      elements.programField.value = state.originalProgram;
      elements.guardianField.value = state.originalGuardian;
      elements.startDateField.value = state.originalStartDate;
      elements.endDateField.value = state.originalEndDate;

      // Disable editing
      elements.titleField.setAttribute('readonly', true);
      elements.descriptionField.setAttribute('readonly', true);
      elements.programField.setAttribute('disabled', true);
      elements.guardianField.setAttribute('disabled', true);
      elements.startDateField.setAttribute('readonly', true);
      elements.endDateField.setAttribute('readonly', true);

      if (elements.updateButton) elements.updateButton.style.display = 'none';
      if (elements.editButton) {
        elements.editButton.textContent = 'Edit';
        elements.editButton.classList.remove('btn--dark-orange');
        elements.editButton.classList.add('btn--blue');
      }

      elements.updateButton.style.display = 'none';
      elements.editButton.textContent = 'Edit';
    }

    state.isEditing = !state.isEditing;
  }

  resetForm() {
    if (this.form) {
      this.form.reset();

      this.formGroups.forEach(group => {
        group.classList.remove('active');
        group.classList.add('is-hidden');
      });
    }
  }

  async updateContractCount() {
    if (!this.countElement) return;

    try {
      const response = await wp.apiFetch({
        path: '/mythguard/v1/contracts?per_page=1',
      });

      if (Array.isArray(response) && response.length > 0) {
        const totalCount = response[0].userContractCount;
        const isAdmin = response[0].isAdmin;
        this.countElement.textContent = isAdmin ? totalCount.toString() : `${totalCount}/5`;
        // Hide the add contract button if user has reached limit and is not admin
        const addButton = document.querySelector('.add-contract');
        if (addButton) {
          addButton.style.display = (!isAdmin && totalCount >= 5) ? 'none' : 'inline-block';
        }
        return;
      }

      const countResponse = await wp.apiFetch({
        path: '/mythguard/v1/contract-count',
      });
      this.countElement.textContent = countResponse.isAdmin ? countResponse.count.toString() : `${countResponse.count}/5`;
    } catch (error) {
      singletonToast.error('Failed to update contract count');
    }
  }

  // Contract Methods
  handleEditContract(clickedElement) {
    const { contractId } = this.getContractElements(clickedElement);
    if (!contractId) {
      singletonToast.show('Contract ID not found', 'error');
      return;
    }
    this.toggleEditMode(contractId);
  }

  async handleAddContract(clickedElement) {
    if (this.isProcessing) return;
    this.isProcessing = true;

    const form = clickedElement.closest('form');
    const title = form.querySelector('.new-contract-title').value;
    const description = form.querySelector('.new-contract-description').value;
    const programId = form.querySelector('.new-contract-program').value;
    const guardianId = form.querySelector('.new-contract-guardian').value;
    const startDate = form.querySelector('.new-contract-start').value;
    const endDate = form.querySelector('.new-contract-end').value;

    if (!title || !programId || !guardianId || !startDate || !endDate) {
      singletonToast.show('Please fill in all required fields', 'error');
      return;
    }

    try {
      const response = await wp.apiFetch({
        path: '/mythguard/v1/contracts',
        method: 'POST',
        data: {
          title,
          content: description,
          meta: {
            related_program: programId,
            related_guardian: guardianId,
            contract_start: startDate,
            contract_end: endDate,
          },
        },
      });

      await this.updateContractCount();
      this.resetForm();
      singletonToast.show('Contract created successfully', 'success');
      window.location.reload();
    } catch (error) {
      console.error('Error creating contract:', error);
      singletonToast.show('Failed to create contract', 'error');
    } finally {
      this.isProcessing = false;
    }
  }

  async handleUpdateContract(clickedElement) {
    if (this.isProcessing) return;
    this.isProcessing = true;

    const {
      contractId,
      titleField,
      descriptionField,
      programField,
      guardianField,
      startDateField,
      endDateField,
    } = this.getContractElements(clickedElement);

    if (!contractId) {
      singletonToast.show('Contract ID not found', 'error');
      return;
    }

    try {
      await wp.apiFetch({
        path: `/mythguard/v1/contracts/${contractId}`,
        method: 'PUT',
        data: {
          title: titleField.value,
          content: descriptionField.value,
          meta: {
            related_program: programField.value,
            related_guardian: guardianField.value,
            contract_start: startDateField.value,
            contract_end: endDateField.value,
          },
        },
      });

      singletonToast.show('Contract updated successfully', 'success');
      window.location.reload();
    } catch (error) {
      let errorMessage = 'Failed to update contract';
      
      if (error.message) {
        errorMessage += `: ${error.message}`;
      } else if (!navigator.onLine) {
        errorMessage = 'No internet connection. Please try again when online.';
      }

      singletonToast.show(errorMessage, 'error');
    } finally {
      this.isProcessing = false;
    }
  }

  async handleDeleteContract(clickedElement) {
    if (this.isProcessing) return;
    this.isProcessing = true;

    const { contractId } = this.getContractElements(clickedElement);

    if (!contractId) {
      singletonToast.show('Contract ID not found', 'error');
      this.isProcessing = false;
      return;
    }

    const confirmed = await singletonToast.confirm('Are you sure you want to delete this contract?');
    if (!confirmed) {
      this.isProcessing = false;
      return;
    }

    try {
      await wp.apiFetch({
        path: `/mythguard/v1/contracts/${contractId}`,
        method: 'DELETE',
      });

      singletonToast.show('Contract deleted successfully', 'success');
      window.location.href = '/contracts';
    } catch (error) {
      console.error('Error deleting contract:', error);
      singletonToast.show('Failed to delete contract', 'error');
    } finally {
      this.isProcessing = false;
    }
  }

}

export default Contract;
