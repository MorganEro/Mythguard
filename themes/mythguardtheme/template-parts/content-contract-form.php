<form class="create-contract-form">
  <div class="create-contract-form__fields">

    <div class="form-group form-group--title floating-label is-hidden">
      <button type="button"
        class="reveal-input-btn"
        aria-controls="contract-title"
        aria-expanded="false">
        Contract Title
      </button>
      <input id="contract-title"
        placeholder="Title" class="new-contract-title" type="text" required>
    </div>
    <div class="form-group form-group--description floating-label is-hidden">
      <button type="button"
        class="reveal-input-btn"
        aria-controls="contract-description"
        aria-expanded="false">
        Description
      </button>
      <textarea id="contract-description"
        placeholder="Description" class="new-contract-description" rows="2"></textarea>
    </div>

    <div class="form-group form-group--program floating-label is-hidden">
      <button type="button"
        class="reveal-input-btn"
        aria-controls="contract-program"
        aria-expanded="false">
        Program
      </button>
      <select id="contract-program" class="new-contract-program" required>
        <option value="">Select Program</option>
        <!-- Options will be populated by JavaScript -->
      </select>
    </div>

    <div class="form-group form-group--guardian floating-label is-hidden">
      <button type="button"
        class="reveal-input-btn"
        aria-controls="contract-guardian"
        aria-expanded="false">
        Guardian
      </button>
      <select id="contract-guardian" class="new-contract-guardian" required>
        <option value="">Select Guardian</option>
        <!-- Options will be populated by JavaScript -->
      </select>
    </div>

    <div class="form-group form-group--date-range floating-label is-hidden">
      <button type="button"
        class="reveal-input-btn"
        aria-controls="contract-date-range"
        aria-expanded="false">
        Contract Dates
      </button>
      <input id="contract-date-range" class="new-contract-date-range" type="text" placeholder="Select dates..." required>
    </div>
  </div>

  <div class="create-contract-form__actions">
    <button data-action="submit-contract" class="btn btn--blue btn--small">Save</button>
    <button type="button" data-action="cancel-contract" class="btn btn--dark-orange btn--small">Cancel</button>
  </div>
</form>