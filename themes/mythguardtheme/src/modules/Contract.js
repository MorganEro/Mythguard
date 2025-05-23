import { singletonToast } from './Toast';
import flatpickr from 'flatpickr';

class Contract {
  constructor() {
    this.isProcessing = false;
    this.form = document.querySelector('.create-contract-form');
    this.countElement = document.querySelector('.contract-count span');
    this.adminCountElement = document.querySelector(
      '.contract-count--admin span'
    );
    this.guardianSelect = document.querySelector('.new-contract-guardian');
    this.programSelect = document.querySelector('.new-contract-program');
    this.formGroups = document.querySelectorAll('.form-group');
    this.revealButtons = document.querySelectorAll('.reveal-input-btn');
    this.formInputs = document.querySelectorAll(
      '.form-group.is-hidden input, .form-group.is-hidden textarea, .form-group.is-hidden select'
    );

    this.state = new Map();
    this.guardians = [];
    this.programs = [];

    this.bindEvents();
    this.updateContractCount();
    this.loadGuardians();
    this.loadPrograms();
    this.setupSelectListeners();
    this.setupRevealFields();
    this.setupDatePickers();
    this.checkAutoEditMode();
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

  setupDatePickers() {
    const startDate = document.querySelector('.new-contract-start-date');
    const endDate = document.querySelector('.new-contract-end-date');

    // Initialize both date pickers with shared config
    const commonConfig = {
      enableTime: true,
      allowInput: true,
      altInput: true,
      altFormat: 'm/d/Y h:i K',
      dateFormat: 'Y-m-d H:i:s',
      minDate: 'today',
      time_24hr: false,
      minuteIncrement: 30,
      disableMobile: true,
    };

    if (startDate) {
      flatpickr(startDate, {
        ...commonConfig,
        enableTime: true,
        allowInput: true,
        altInput: true,
        altFormat: 'm/d/Y h:i K',
        dateFormat: 'Y-m-d H:i',
        minDate: 'today',
        time_24hr: false,
        minuteIncrement: 30,
        defaultHour: 9,
        placeholder: 'Select start date...',
        onChange: selectedDates => {
          if (selectedDates[0]) {
            // Update end date min date when start date changes
            const endDatePicker = document.querySelector(
              '.new-contract-end-date'
            )._flatpickr;
            if (endDatePicker) {
              endDatePicker.set('minDate', selectedDates[0]);
            }
            startDate.dispatchEvent(new Event('change'));
          }
        },
      });
    }

    if (endDate) {
      flatpickr(endDate, {
        ...commonConfig,
        defaultHour: 17, // Default to 5 PM for end time
        placeholder: 'Select end date...',
        onChange: selectedDates => {
          if (selectedDates[0]) {
            endDate.dispatchEvent(new Event('change'));
          }
        },
      });
    }
  }

  setupRevealFields() {
    // Add click handlers to reveal buttons
    this.revealButtons.forEach(button => {
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

  updateProgramOptions(programs, targetSelect = null) {
    const select = targetSelect || this.programSelect;
    if (!select) return;

    // Store current value
    const currentValue = select.value;

    // Clear existing options
    select.innerHTML = '';

    // Add placeholder only for new contract form
    if (select === this.programSelect) {
      const placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = 'Select a program...';
      select.appendChild(placeholder);
    }

    // Add program options
    programs.forEach(program => {
      const option = document.createElement('option');
      option.value = program.id;
      option.textContent = program.title.rendered;
      select.appendChild(option);
    });

    // Restore value if it exists
    if (currentValue) {
      select.value = currentValue;
    }
  }

  updateGuardianOptions(guardians, targetSelect = null) {
    const select = targetSelect || this.guardianSelect;
    if (!select) return;

    // Store current value
    const currentValue = select.value;

    // Clear existing options
    select.innerHTML = '';

    // Add placeholder only for new contract form
    if (select === this.guardianSelect) {
      const placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = 'Select a guardian...';
      select.appendChild(placeholder);
    }

    // Add guardian options
    guardians.forEach(guardian => {
      const option = document.createElement('option');
      option.value = guardian.id;
      option.textContent = guardian.title.rendered;
      select.appendChild(option);
    });

    // Restore value if it exists
    if (currentValue) {
      select.value = currentValue;
    }
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
    const contractItem =
      clickedElement.closest('li') ||
      clickedElement.closest('.single-contract-item');

    if (!contractItem) return null;
    return {
      contractItem,
      titleField: contractItem.querySelector(
        '.contract-title-field, .single-contract-title-field'
      ),
      descriptionField: contractItem.querySelector(
        '.contract-description-field, .single-contract-description-field'
      ),
      programField: contractItem.querySelector(
        '.contract-program-field, .single-contract-program-field'
      ),
      guardianField: contractItem.querySelector(
        '.contract-guardian-field, .single-contract-guardian-field'
      ),
      startDateField: contractItem.querySelector(
        '.contract-start-date, .single-contract-start-date'
      ),
      endDateField: contractItem.querySelector(
        '.contract-end-date, .single-contract-end-date'
      ),

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

    // Toggle the editing state
    if (!state.isEditing) {
      // Save current values before editing
      state.originalTitle = elements.titleField.value;
      state.originalDescription = elements.descriptionField.value;
      state.originalProgram = elements.programField.value;
      state.originalGuardian = elements.guardianField.value;
      state.originalStartDate = elements.startDateField.value;
      state.originalEndDate = elements.endDateField.value;

      // Enable editing
      elements.titleField.removeAttribute('readonly');
      elements.descriptionField.removeAttribute('readonly');
      elements.programField.removeAttribute('disabled');
      elements.guardianField.removeAttribute('disabled');
      elements.startDateField.removeAttribute('readonly');
      elements.endDateField.removeAttribute('readonly');

      // Update program and guardian options
      this.updateProgramOptions(this.programs, elements.programField);
      this.updateGuardianOptions(this.guardians, elements.guardianField);

      // Get today's date at midnight for comparison
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      // Parse the dates from m/d/Y h:i A format
      const startDateValue = elements.startDateField.value;
      const endDateValue = elements.endDateField.value;
      
      // Parse dates using the display format
      const parsedStartDate = flatpickr.parseDate(startDateValue, 'm/d/Y h:i A');
      const parsedEndDate = flatpickr.parseDate(endDateValue, 'm/d/Y h:i A');
      
      // Initialize Flatpickr on start date field
      const fp = flatpickr(elements.startDateField, {
        enableTime: true,
        allowInput: true,
        altInput: true,
        altFormat: 'm/d/Y h:i K',
        dateFormat: 'Y-m-d H:i:s',
        time_24hr: false,
        minuteIncrement: 30,
        defaultDate: parsedStartDate,
        minDate: today,
        onChange: selectedDates => {
          if (selectedDates[0]) {
            // Update end date min date when start date changes
            const endDatePicker = elements.endDateField._flatpickr;
            if (endDatePicker) {
              endDatePicker.set('minDate', selectedDates[0]);
            }
          }
        },
      });

      // Initialize Flatpickr on end date field
      const minEndDate = Math.max(
        parsedStartDate?.getTime() || today.getTime(),
        today.getTime()
      );

      const fp2 = flatpickr(elements.endDateField, {
        enableTime: true,
        allowInput: true,
        altInput: true,
        altFormat: 'm/d/Y h:i K',
        dateFormat: 'Y-m-d H:i:s',
        time_24hr: false,
        minuteIncrement: 30,
        defaultDate: parsedEndDate,
        minDate: new Date(minEndDate),
      });

      // Update button states
      if (elements.updateButton) {
        elements.updateButton.style.display = 'inline-block';
      }
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

      // Destroy Flatpickr instance and ensure readonly
      if (elements.startDateField._flatpickr) {
        elements.startDateField._flatpickr.destroy();
      }
      elements.startDateField.setAttribute('readonly', true);

      if (elements.endDateField._flatpickr) {
        elements.endDateField._flatpickr.destroy();
      }
      elements.endDateField.setAttribute('readonly', true);

      if (elements.updateButton) {
        elements.updateButton.style.display = 'none';
      }
      if (elements.editButton) {
        elements.editButton.textContent = 'Edit';
        elements.editButton.classList.remove('btn--orange');
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
      const countResponse = await wp.apiFetch({
        path: '/mythguard/v1/contract-count',
      });

      // Update user's contract count
      const totalCount = countResponse.userCount;
      const activeCount = countResponse.activeCount;
      this.countElement.textContent = countResponse.isAdmin
        ? `${activeCount} active / ${totalCount} total`
        : `${activeCount}/5 active (${totalCount} total)`;

      // Update total count for admin
      if (countResponse.isAdmin && this.adminCountElement) {
        this.adminCountElement.textContent =
          countResponse.totalCount.toString();
        this.adminCountElement.parentElement.style.display = 'grid';
      } else if (this.adminCountElement) {
        this.adminCountElement.parentElement.style.display = 'none';
      }

      // Hide the add contract button if user has reached active contract limit and is not admin
      const addButton = document.querySelector('.add-contract');
      if (addButton) {
        addButton.style.display =
          !countResponse.isAdmin && activeCount >= 5 ? 'none' : 'inline-block';
      }
    } catch (error) {
      singletonToast.error('Failed to update contract count');
    }
  }

  // Check if we should automatically enter edit mode
  async checkAutoEditMode() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('edit') === 'true') {
      // Wait for guardians and programs to load
      await Promise.all([this.loadGuardians(), this.loadPrograms()]);

      const contractItem = document.querySelector('[data-id]');
      if (contractItem) {
        this.handleEditContract(
          contractItem.querySelector('[data-action="edit-contract"]')
        );
      }
    }
  }

  // Contract Methods
  handleEditContract(clickedElement) {
    const contractItem = clickedElement.closest('[data-id]');
    const contractId = contractItem.dataset.id;
    const state = this.getContractState(contractId);

    // Check if contract is expired
    const isExpired = contractItem.classList.contains('expired-contract');
    if (isExpired) {
      singletonToast.error('Cannot edit expired contracts');
      return;
    }

    if (state.isEditing) {
      // Cancel editing
      this.toggleEditMode(contractId);
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
    const startDatePicker = form.querySelector(
      '.new-contract-start-date'
    )._flatpickr;
    const endDatePicker = form.querySelector(
      '.new-contract-end-date'
    )._flatpickr;

    if (
      !startDatePicker?.selectedDates[0] ||
      !endDatePicker?.selectedDates[0]
    ) {
      singletonToast.show('Please select both start and end dates', 'error');
      this.isProcessing = false;
      return;
    }

    const startDate = startDatePicker.formatDate(
      startDatePicker.selectedDates[0],
      'Y-m-d H:i:s'
    );
    const endDate = endDatePicker.formatDate(
      endDatePicker.selectedDates[0],
      'Y-m-d H:i:s'
    );

    if (!title || !programId || !guardianId) {
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

    const startDatePicker = startDateField._flatpickr;
    const endDatePicker = endDateField._flatpickr;

    if (
      !startDatePicker?.selectedDates[0] ||
      !endDatePicker?.selectedDates[0]
    ) {
      singletonToast.show('Please select both start and end dates', 'error');
      this.isProcessing = false;
      return;
    }

    const startDate = startDatePicker.formatDate(
      startDatePicker.selectedDates[0],
      'Y-m-d H:i:s'
    );
    const endDate = endDatePicker.formatDate(
      endDatePicker.selectedDates[0],
      'Y-m-d H:i:s'
    );

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
            contract_start: startDate,
            contract_end: endDate,
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

    const confirmed = await singletonToast.confirm(
      'Are you sure you want to delete this contract?'
    );
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
