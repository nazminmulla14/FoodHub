<div class="container-fluid bg-white p-0">
    <div class="container-fluid py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h5 class="section-title text-primary">Contact Us</h5>
                <h1>Contact For Any Query</h1>
            </div>

            @if ($successMessage)
                <div class="alert alert-success">{{ $successMessage }}</div>
            @endif

            <div class="row g-4">
                <div class="col-md-6">
                    <iframe class="position-relative rounded w-100 h-100"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3001156.4288297426!2d-78.01371936852176!3d42.72876761954724!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4ccc4bf0f123a5a9%3A0xddcfc6c1de189567!2sNew%20York%2C%20USA!5e0!3m2!1sen!2sbd!4v1603794290143!5m2!1sen!2sbd"
                        style="min-height: 350px; border:0;" allowfullscreen loading="lazy"></iframe>
                </div>
                <div class="col-md-6">
                    <form wire:submit="send">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Your name</label>
                                <input type="text" class="form-control" wire:model="name">
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" wire:model="email">
                                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Subject</label>
                                <input type="text" class="form-control" wire:model="subject">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message</label>
                                <textarea class="form-control" rows="5" wire:model="message"></textarea>
                                @error('message') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100 py-3" wire:loading.attr="disabled">Send message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
