<?php
/**
 * BROMO SUNRISE TOUR — SINGLE FILE NATIVE PHP
 *
 * Dummy data and helper names are intentionally generic so they can be replaced
 * with the model/controller variables and functions already present in your app.
 */

function dummy_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Replace this entire array with data from your existing controller/model.
$dummyTrip = [
    'brand'       => 'Explores Java',
    'title'       => 'Bromo Sunrise Tour',
    'location'    => 'East Java',
    'rating'      => '4.8',
    'reviews'     => '320',
    'description' => 'Witness the spectacular sunrise over Mount Bromo and explore its stunning volcanic landscape.',
    'duration'    => '2 Days / 1 Night',
    'difficulty'  => 'Easy',
    'group_type'  => 'Private / Join-in',
    'availability'=> 'Year Round',
    'price'       => 129,
    'currency'    => '$',
    'hero_image'  => '/assets/images/bromo-hero-sunrise.jpg',
    'video_image' => '/assets/images/bromo-jeep-video.jpg',
];

$dummyHighlights = [
    ['icon' => '☀', 'title' => 'Bromo Sunrise', 'copy' => 'View Point'],
    ['icon' => '⌁', 'title' => 'Sea of Sand &', 'copy' => 'Bromo Crater'],
    ['icon' => '♧', 'title' => 'Madakaripura', 'copy' => 'Waterfall'],
    ['icon' => '♙', 'title' => 'Local Guide', 'copy' => 'Experience'],
];

$dummyItinerary = [
    [
        'day' => 'DAY 1',
        'title' => 'Surabaya / Malang – Bromo – Sunrise – Crater – Hotel',
        'image' => '/assets/images/bromo-sea-of-sand.jpg',
        'steps' => [
            '00:30 – Pick up from Surabaya or Malang',
            '02:30 – Arrive at Penanjakan (Sunrise View Point)',
            'Watch the amazing sunrise over Mount Bromo',
            '04:30 – Explore the Sea of Sand by 4WD Jeep',
            'Hike to the Bromo Crater',
            '09:00 – Back to hotel for breakfast and rest',
            '12:00 – Free time',
            'Overnight at hotel in Bromo area',
        ],
    ],
    [
        'day' => 'DAY 2',
        'title' => 'Madakaripura Waterfall – Surabaya / Malang',
        'image' => '/assets/images/madakaripura-waterfall.jpg',
        'steps' => [
            '06:30 – Breakfast and check out',
            '07:30 – Visit Madakaripura Waterfall',
            '10:00 – Return trip to Surabaya or Malang',
            '15:00 – Arrive and tour ends',
        ],
    ],
];

$dummyIncluded = [
    '1 night hotel accommodation (twin sharing)',
    'Daily breakfast',
    'Private transportation & 4WD Jeep',
    'Local English-speaking guide',
    'Entrance fees & parking',
    'Mineral water during the tour',
];

$dummyExcluded = [
    'Flights / Train tickets',
    'Lunch & Dinner',
    'Travel insurance',
    'Personal expenses',
    'Tipping for guide & driver (optional)',
];
?>
<link rel="stylesheet" href="assets/css/detail.css">
        <section class="hero" aria-labelledby="tourTitle">
            <img class="hero-image" src="<?= dummy_escape($dummyTrip['hero_image']) ?>" alt="Mount Bromo at sunrise">
            <div class="shell">
                <div class="hero-copy">
                    <h1 id="tourTitle"><?= dummy_escape($dummyTrip['title']) ?></h1>
                    <div class="meta-row">
                        <span><strong>⌖</strong> <?= dummy_escape($dummyTrip['location']) ?></span>
                        <span class="rating"><span class="star">★</span><?= dummy_escape($dummyTrip['rating']) ?> (<?= dummy_escape($dummyTrip['reviews']) ?> reviews)</span>
                    </div>
                    <p class="hero-content"><?= dummy_escape($dummyTrip['description']) ?></p>
                    <div class="detail-row">
                        <span class="detail-item"><span class="detail-icon">▣</span><?= dummy_escape($dummyTrip['duration']) ?></span>
                        <span class="detail-item"><span class="detail-icon">♧</span><?= dummy_escape($dummyTrip['difficulty']) ?></span>
                        <span class="detail-item"><span class="detail-icon">♙</span><?= dummy_escape($dummyTrip['group_type']) ?></span>
                        <span class="detail-item"><span class="detail-icon">◷</span><?= dummy_escape($dummyTrip['availability']) ?></span>
                    </div>
                    <div class="hero-btns">
                        <a class="button-primary" href="#booking">Book Now</a>
                        <button class="button-outline js-scroll" type="button" data-target="#customize">Customize This Tour</button>
                    </div>
                </div>
            </div>
        </section>

        <nav class="tab-band" aria-label="Tour details navigation">
            <div class="shell tabs">
                <a class="tab active" href="#overview">Overview</a>
                <a class="tab" href="#itinerary">Itinerary</a>
                <a class="tab" href="#inclusions">Inclusions</a>
                <a class="tab" href="#accommodation">Accommodation</a>
                <a class="tab" href="#important-info">Important Info</a>
                <a class="tab" href="#reviews">Reviews</a>
                <a class="tab" href="#faq">FAQ</a>
            </div>
        </nav>

        <div class="shell content-grid">
            <article>
                <section class="page-section section-anchor" id="overview">
                    <h2>Overview</h2>
                    <p class="overview-copy">This 2D1N tour is perfect for travelers who want to experience the breathtaking beauty of Mount Bromo. Enjoy a mesmerizing sunrise view, explore the Sea of Sand, and visit the stunning Madakaripura Waterfall.</p>
                    <h3>Highlights</h3>
                    <div class="highlight-grid">
                        <?php foreach ($dummyHighlights as $highlight): ?>
                            <div class="highlight">
                                <span class="highlight-icon" aria-hidden="true"><?= dummy_escape($highlight['icon']) ?></span>
                                <div class="highlight-text"><span><?= dummy_escape($highlight['title']) ?></span><span><?= dummy_escape($highlight['copy']) ?></span></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="page-section section-anchor" id="itinerary">
                    <h2>Itinerary</h2>
                    <div class="itinerary-list">
                        <?php foreach ($dummyItinerary as $day): ?>
                            <article class="itinerary-card">
                                <div class="itinerary-image"><img src="<?= dummy_escape($day['image']) ?>" alt="<?= dummy_escape($day['title']) ?>"></div>
                                <div class="itinerary-info">
                                    <div class="itinerary-heading">
                                        <span class="day-badge"><?= dummy_escape($day['day']) ?></span>
                                        <h4><?= dummy_escape($day['title']) ?></h4>
                                    </div>
                                    <ul class="timeline">
                                        <?php foreach ($day['steps'] as $step): ?><li><?= dummy_escape($step) ?></li><?php endforeach; ?>
                                    </ul>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="custom-card" id="customize">
                        <span class="custom-icon" aria-hidden="true">♧</span>
                        <div><strong>Want to extend your trip?</strong><span>We can customize the itinerary to include Ijen Crater, Kawah Ijen Blue Fire, Yogyakarta, or other amazing destinations in Java.</span></div>
                        <button class="button-outline js-dummy-action" type="button" data-message="Dummy action: Customization form would open here.">Customize Your Trip</button>
                    </div>
                </section>

                <section class="section-anchor" id="inclusions">
                    <div class="included-band">
                        <div class="benefit-column included">
                            <h3>What’s Included</h3>
                            <ul class="benefit-list">
                                <?php foreach ($dummyIncluded as $item): ?><li><span class="bullet">●</span><span><?= dummy_escape($item) ?></span></li><?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="benefit-column excluded">
                            <h3>What’s Not Included</h3>
                            <ul class="benefit-list">
                                <?php foreach ($dummyExcluded as $item): ?><li><span class="bullet">⊗</span><span><?= dummy_escape($item) ?></span></li><?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="video-card">
                            <img src="<?= dummy_escape($dummyTrip['video_image']) ?>" alt="Bromo tour video preview">
                            <button class="play-button js-dummy-action" type="button" data-message="Dummy action: Video modal would open here." aria-label="Play Bromo Tour video">▶</button>
                            <span class="video-caption">Watch Bromo Tour Video</span>
                        </div>
                    </div>
                </section>
            </article>

            <aside class="sidebar" id="booking">
                <section class="booking-card" aria-label="Booking panel">
                    <span class="from-label">From</span>
                    <div class="price"><span id="priceAmount"><?= dummy_escape($dummyTrip['currency']) ?><?= dummy_escape($dummyTrip['price']) ?></span><small>/ person</small></div>
                    <div class="guarantee"><span>◉</span> Best Price Guarantee</div>

                    <div class="booking-field">
                        <span class="field-label">Tour Type</span>
                        <div class="type-selector" role="group" aria-label="Tour type">
                            <button class="type-option active" type="button" data-tour-type="Join-in Tour">Join-in Tour</button>
                            <button class="type-option" type="button" data-tour-type="Private Tour">Private Tour</button>
                        </div>
                    </div>
                    <div class="booking-field">
                        <span class="field-label">Travelers</span>
                        <div class="traveler-control">
                            <span class="traveler-label" id="travelerLabel">2 Travelers</span>
                            <button class="quantity-button" type="button" data-quantity="decrease" aria-label="Decrease travelers">−</button>
                            <button class="quantity-button" type="button" data-quantity="increase" aria-label="Increase travelers">+</button>
                        </div>
                    </div>
                    <button class="button-primary js-dummy-action" type="button" data-message="Dummy action: Send booking request to your existing controller.">Book Now</button>
                    <button class="whatsapp-button js-dummy-action" type="button" data-message="Dummy action: Open WhatsApp inquiry with current trip data.">◔ Enquire via WhatsApp</button>
                    <p class="status-message" id="bookingStatus" aria-live="polite"></p>
                </section>

                <section class="why-card">
                    <h3>Why Book With Us?</h3>
                    <ul class="why-list">
                        <li>Local expert guides</li>
                        <li>No hidden charges</li>
                        <li>Free cancellation (T&amp;C apply)</li>
                        <li>24/7 customer support</li>
                        <li>Trusted by 1000+ travelers</li>
                    </ul>
                </section>

                <section class="info-card" id="important-info">
                    <h3>Quick Info</h3>
                    <dl class="info-list">
                        <div><dt>Duration</dt><dd>2 Days / 1 Night</dd></div>
                        <div><dt>Start Point</dt><dd>Surabaya / Malang</dd></div>
                        <div><dt>End Point</dt><dd>Surabaya / Malang</dd></div>
                        <div><dt>Tour Availability</dt><dd>Everyday</dd></div>
                        <div><dt>Minimum Pax</dt><dd>1 Person</dd></div>
                    </dl>
                </section>
            </aside>
        </div>

        <!-- Placeholder anchors for sections which are intentionally out of scope in this one-page dummy. -->
        <span id="accommodation" class="section-anchor"></span>
        <span id="reviews" class="section-anchor"></span>
        <span id="faq" class="section-anchor"></span>
    </main>

    <section class="bottom-cta">
        <div class="shell bottom-cta-inner">
            <span class="plane-icon" aria-hidden="true">➤</span>
            <div><strong>Need a different itinerary or have special requests?</strong><span>Our travel experts are happy to help you build your perfect trip.</span></div>
            <button class="button-outline js-dummy-action" type="button" data-message="Dummy action: Custom trip enquiry would open here.">Plan Your Custom Trip</button>
        </div>
    </section>

