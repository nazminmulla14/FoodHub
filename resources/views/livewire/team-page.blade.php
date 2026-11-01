<div class="container-fluid bg-white p-0">
    <div class="container-fluid pt-5 pb-3">
        <div class="container">
            <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                <h5 class="section-title ff-secondary text-center text-primary fw-normal">Team Members</h5>
                <h1 class="mb-5">Our Master Chefs</h1>
            </div>
            <div class="row g-4">
                @foreach ($team as $index => $member)
                    <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="{{ 0.1 + ($index * 0.2) }}s">
                        <div class="team-item text-center rounded overflow-hidden">
                            <div class="rounded-circle overflow-hidden m-4">
                                <img class="img-fluid" src="{{ asset($member['image']) }}" alt="{{ $member['name'] }}">
                            </div>
                            <h5 class="mb-0">{{ $member['name'] }}</h5>
                            <small>{{ $member['role'] }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
