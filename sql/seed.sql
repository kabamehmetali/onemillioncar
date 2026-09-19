-- Lucid Auto Haus — seed data (settings, admin user, demo inventory, testimonials, FAQs)
USE onemillioncar_db;

-- Default admin login: admin / admin123  (change it under Admin → Account)
INSERT INTO users (username, password_hash, display_name) VALUES
('admin', '$2y$10$ZQS2dgAgfsooTJdXWIcTWu4bMNn/xy30.tijSXET4q.71thVIYqFa', 'Administrator');

INSERT INTO settings (`key`, `value`) VALUES
('site_name', 'Lucid Auto Haus'),
('tagline', 'Clarity in every deal.'),
('agent_name', 'Bünyamin Akkaya'),
('agent_title', 'Sales & Leasing Consultant'),
('agent_bio_short', 'I help drivers across the Greater Toronto Area find the right vehicle at the right price — without the pressure, the games or the fine print. Every car I sell comes with straight answers, a full inspection report and my personal number.'),
('agent_bio', 'I got into the car business for a simple reason: I love cars, and I hated how it felt to buy one. Long waits, vague numbers, a manager behind a curtain — none of it served the person actually signing the paperwork.

Lucid Auto Haus is my answer to that. It is a one-on-one relationship with a consultant who knows the market, does the legwork and tells you exactly what a car is worth before you ever sit in it. I hand-select every vehicle in my inventory, inspect it with a licensed technician, and price it transparently against the current market.

Whether you are buying your first car, upgrading the family SUV or hunting a specific spec that has to be sourced from out of province, you will deal with me from the first message to the day I hand you the keys — and long after.'),
('years_experience', '9'),
('cars_sold', '1,200'),
('google_rating', '5.0'),
('phone', '+16479368096'),
('whatsapp', '16479368096'),
('email', 'hello@lucidautohaus.ca'),
('notify_email', ''),
('address_line', '4-8044 Dixie Rd'),
('city', 'Brampton, ON L6T 5G8'),
('service_area', 'Toronto · Mississauga · Brampton · Vaughan · Markham · Oakville'),
('hours_weekdays', '9:00 AM – 7:00 PM'),
('hours_saturday', '10:00 AM – 5:00 PM'),
('hours_sunday', 'By appointment'),
('google_maps_url', 'https://maps.app.goo.gl/grFX7yVVXRX6CzHQA'),
('google_place_id', 'ChIJEewRRSE_K4gRxGGdUH3Tc58'),
('google_maps_embed', 'https://www.google.com/maps?q=43.6990399,-79.7089&z=17&output=embed'),
('social_instagram', 'https://instagram.com/'),
('social_facebook', 'https://facebook.com/'),
('social_tiktok', ''),
('social_youtube', ''),
('social_linkedin', 'https://linkedin.com/'),
('hero_home_kicker', 'Toronto & GTA · Pre-Owned Specialist'),
('hero_home_title', 'The right car. The right price. No games.'),
('hero_home_text', 'Hand-picked, fully inspected vehicles and a consultant who works for you — not the dealership.'),
('hero_inventory_title', 'Current Inventory'),
('hero_inventory_text', 'Every vehicle below has been personally inspected and market-priced. Reserve a test drive in under a minute.'),
('hero_about_title', 'A better way to buy a car'),
('hero_about_text', 'Straight talk, transparent pricing and a consultant who picks up the phone.'),
('finance_rate_default', '7.99'),
('finance_term_default', '72'),
('meta_description', 'Lucid Auto Haus — hand-picked, inspected pre-owned vehicles in Toronto and the GTA with transparent pricing, financing for every credit situation and top-dollar trade-ins.'),
('footer_note', 'All prices are in Canadian dollars and exclude HST and licensing. Financing available OAC. Vehicle availability subject to prior sale.'),
-- SMS alerts. Twilio credentials are deliberately blank here: enter them in
-- Admin -> Settings -> SMS alerts so no secret is ever committed to the repo.
('sms_enabled', '0'),
('sms_notify_number', ''),
('twilio_account_sid', ''),
('twilio_auth_token', ''),
('twilio_from_number', ''),
-- reCAPTCHA v3 on the public forms. The secret key is deliberately blank here:
-- enter both keys in Admin -> Settings -> reCAPTCHA so no secret reaches the repo.
('recaptcha_enabled', '0'),
('recaptcha_site_key', ''),
('recaptcha_secret_key', ''),
('recaptcha_min_score', '0.5');

-- ----------------------------------------------------------------- vehicles
INSERT INTO vehicles
(slug, year, make, model, trim, body_type, `condition`, status, price, sale_price, mileage, transmission, drivetrain, fuel_type, engine, horsepower, fuel_economy, exterior_color, interior_color, color_hex, doors, seats, vin, stock_number, description, features, cover_image, is_featured, sort_order) VALUES
('2022-bmw-330i-xdrive-m-sport', 2022, 'BMW', '330i', 'xDrive M Sport', 'Sedan', 'certified', 'available', 46900.00, NULL, 28400, 'Automatic', 'AWD', 'Gasoline', '2.0L TwinPower Turbo I4', 255, '8.4 L/100 km combined', 'Black Sapphire Metallic', 'Cognac Vernasca Leather', '#0e0f14', 4, 5, 'WBA5R7C0XNFK21847', 'LAH-1042',
'One-owner, no-accident 330i with the M Sport package and the Premium Package Enhanced. Full BMW service history, both keys, and fresh Michelin Pilot Sport 4S tires installed in the spring. Warranty coverage until March 2027 or 80,000 km.

This is the spec everyone asks for: xDrive for Ontario winters, the M Sport brakes and suspension, and the cognac interior that makes the black paint pop. Drives like new.',
'M Sport Package
Premium Package Enhanced
Live Cockpit Professional 12.3\" digital cluster
Harman Kardon surround sound
Heated steering wheel & heated front seats
Head-up display
Adaptive LED headlights
Parking Assistant Plus with surround view
Wireless Apple CarPlay & Android Auto
Two keys, all books, full service history',
'uploads/vehicles/2022-bmw-330i-xdrive-m-sport-1.jpg', 1, 1),

('2021-mercedes-benz-c-300-4matic-amg-line', 2021, 'Mercedes-Benz', 'C 300', '4MATIC AMG Line', 'Sedan', 'used', 'available', 44500.00, 42900.00, 35200, 'Automatic', 'AWD', 'Gasoline', '2.0L Turbo I4 with EQ Boost', 255, '9.0 L/100 km combined', 'Polar White', 'Black ARTICO', '#e6e8ea', 4, 5, 'W1KWF8EB5MR612093', 'LAH-1038',
'Polar White over black with the AMG Line exterior, 19\" AMG wheels and the Premium Package. Clean CARFAX, Ontario vehicle from new, serviced exclusively at Mercedes-Benz. Winter tires on rims included.',
'AMG Line exterior & interior
Premium Package with Burmester audio
Panoramic sunroof
19\" AMG multi-spoke wheels
Blind Spot Assist & Active Brake Assist
360° camera
Heated front seats
Ambient lighting (64 colours)
Wireless charging
Winter tire package included',
'uploads/vehicles/2021-mercedes-benz-c-300-4matic-amg-line-1.jpg', 0, 2),

('2022-audi-q5-45-tfsi-progressiv', 2022, 'Audi', 'Q5', '45 TFSI quattro Progressiv', 'SUV', 'certified', 'available', 49800.00, NULL, 24900, 'Dual-Clutch', 'AWD', 'Gasoline', '2.0L TFSI I4', 261, '9.3 L/100 km combined', 'Navarra Blue Metallic', 'Rock Grey Leather', '#1f3a6b', 4, 5, 'WA1BAAFY3N2041588', 'LAH-1051',
'Low-kilometre Q5 in the sought-after Navarra Blue. Audi Certified :plus with the balance of the 5-year/100,000 km warranty. Tow package fitted from the factory, panoramic roof, and the Virtual Cockpit.

A perfect family SUV: quattro all-wheel drive, adaptive cruise for the 401 commute, and a cabin that still feels expensive.',
'Audi Certified :plus warranty
quattro all-wheel drive
Virtual Cockpit 12.3\" display
Panoramic sunroof
Audi pre sense front & rear
Adaptive cruise assist
Heated front & rear seats
Power tailgate with kick sensor
Factory tow package (2,000 kg)
Bang & Olufsen 3D sound',
'uploads/vehicles/2022-audi-q5-45-tfsi-progressiv-1.jpg', 1, 3),

('2023-toyota-rav4-hybrid-xle', 2023, 'Toyota', 'RAV4 Hybrid', 'XLE AWD', 'SUV', 'used', 'available', 42900.00, NULL, 18700, 'CVT', 'AWD', 'Hybrid', '2.5L Hybrid I4', 219, '6.0 L/100 km combined', 'Magnetic Grey Metallic', 'Black SofTex', '#5c6066', 4, 5, '2T3RWRFV0PW184472', 'LAH-1055',
'Barely broken-in RAV4 Hybrid XLE with 18,700 km and the balance of Toyota\'s factory warranty. Electronic on-demand AWD, 6.0 L/100 km, and the reliability that keeps these on wait-lists at franchise dealers.',
'Toyota Safety Sense 2.5+
Electronic On-Demand AWD
8\" touchscreen with Apple CarPlay & Android Auto
Power moonroof
Heated front seats & steering wheel
Blind Spot Monitor with Rear Cross Traffic Alert
Dual-zone climate control
Power liftgate
Balance of factory warranty',
'uploads/vehicles/2023-toyota-rav4-hybrid-xle-1.jpg', 1, 4),

('2021-honda-civic-touring', 2021, 'Honda', 'Civic', 'Touring', 'Sedan', 'used', 'available', 28400.00, NULL, 41300, 'CVT', 'FWD', 'Gasoline', '1.5L Turbo I4', 174, '7.1 L/100 km combined', 'Rallye Red', 'Black Leather', '#b3121e', 4, 5, '2HGFC1F91MH302517', 'LAH-1029',
'Top-trim Civic Touring in Rallye Red. Leather, navigation, Honda Sensing, and a spotless interior. One owner, non-smoker, all maintenance done at Honda. A superb first car or daily commuter.',
'Honda Sensing suite
Leather-trimmed seats, heated front & rear
Navigation with 7\" display
Power moonroof
Premium 452-watt audio
LED headlights
Remote engine starter
Wireless charging
Apple CarPlay & Android Auto',
'uploads/vehicles/2021-honda-civic-touring-1.jpg', 0, 5),

('2022-tesla-model-3-long-range', 2022, 'Tesla', 'Model 3', 'Long Range AWD', 'Sedan', 'used', 'available', 47900.00, NULL, 31000, 'Automatic', 'AWD', 'Electric', 'Dual Motor Electric', 346, '~560 km range (EPA est.)', 'Pearl White Multi-Coat', 'Black Premium Interior', '#eceef0', 4, 5, '5YJ3E1EB6NF291064', 'LAH-1047',
'Dual-motor Long Range Model 3 with the Premium interior, 19\" Sport wheels and Enhanced Autopilot. Battery health reads 96% on the latest check. Includes the mobile connector and a Level 2 home charger.

Instant torque, no gas bills and the best charging network in the country.',
'Dual Motor All-Wheel Drive
~560 km estimated range
Enhanced Autopilot
Premium interior with 14-speaker audio
Glass roof
Heated front & rear seats
19\" Sport wheels
Level 2 home charger included
Supercharger network access
8-year battery & drive-unit warranty',
'uploads/vehicles/2022-tesla-model-3-long-range-1.jpg', 1, 6),

('2020-porsche-macan-s', 2020, 'Porsche', 'Macan', 'S', 'SUV', 'used', 'available', 67500.00, NULL, 39800, 'Dual-Clutch', 'AWD', 'Gasoline', '3.0L Twin-Turbo V6', 348, '11.5 L/100 km combined', 'Volcano Grey Metallic', 'Black Leather', '#3d3f44', 4, 5, 'WP1AB2A55LLB39521', 'LAH-1036',
'A properly specified Macan S: Sport Chrono, air suspension, 21\" RS Spyder wheels, Bose audio and the Premium Package Plus. Porsche-serviced since new with a Porsche Approved pre-owned inspection completed in June.

This is the one to buy if you want a family SUV that still makes you take the long way home.',
'Sport Chrono Package
Adaptive air suspension with PASM
21\" RS Spyder Design wheels
Bose surround sound
Premium Package Plus
Panoramic roof
14-way power seats with memory
Lane Change Assist
Porsche Connect with Apple CarPlay
Full Porsche service history',
'uploads/vehicles/2020-porsche-macan-s-1.jpg', 1, 7),

('2022-ford-f-150-lariat-supercrew', 2022, 'Ford', 'F-150', 'Lariat SuperCrew 4x4', 'Truck', 'used', 'available', 58900.00, 56900.00, 33600, 'Automatic', '4WD', 'Gasoline', '3.5L EcoBoost V6', 400, '12.0 L/100 km combined', 'Antimatter Blue Metallic', 'Black Leather', '#1b2a4a', 4, 5, '1FTFW1E82NFA20315', 'LAH-1044',
'Lariat SuperCrew with the 3.5L EcoBoost, Max Trailer Tow Package, Pro Power Onboard and the 502A Equipment Group. Spray-in bedliner, tonneau cover and running boards. Never used for commercial work — clean, tight and ready.',
'3.5L EcoBoost V6 — 400 hp / 500 lb-ft
Max Trailer Tow Package (5,900 kg)
Pro Power Onboard 2.0 kW
502A Equipment Group
12\" touchscreen with SYNC 4
B&O sound system
Heated & ventilated leather seats
360° camera with Pro Trailer Backup Assist
Spray-in bedliner & tonneau cover
Twin-panel moonroof',
'uploads/vehicles/2022-ford-f-150-lariat-supercrew-1.jpg', 0, 8),

('2021-lexus-rx-350-luxury', 2021, 'Lexus', 'RX 350', 'Luxury AWD', 'SUV', 'certified', 'available', 51900.00, NULL, 36500, 'Automatic', 'AWD', 'Gasoline', '3.5L V6', 295, '10.8 L/100 km combined', 'Eminent White Pearl', 'Noble Brown Leather', '#e9e9e6', 4, 5, '2T2HZMDA4MC281735', 'LAH-1033',
'Lexus Certified RX 350 Luxury in the rare Eminent White Pearl over Noble Brown. Mark Levinson audio, heads-up display, panoramic view monitor and the extended warranty to 6 years / 110,000 km.',
'Lexus Certified Pre-Owned
Mark Levinson 15-speaker audio
Head-up display
Panoramic View Monitor
Heated & ventilated front seats
Heated rear seats
Power moonroof
Triple-beam LED headlights
Lexus Safety System+ 2.0
Wireless charging',
'uploads/vehicles/2021-lexus-rx-350-luxury-1.jpg', 0, 9),

('2023-hyundai-tucson-preferred-trend', 2023, 'Hyundai', 'Tucson', 'Preferred AWD w/ Trend Package', 'SUV', 'used', 'available', 36700.00, NULL, 15400, 'Automatic', 'AWD', 'Gasoline', '2.5L GDI I4', 187, '9.0 L/100 km combined', 'Amazon Grey', 'Black Cloth', '#6a6d6a', 4, 5, '5NMJFCAE1PH214690', 'LAH-1058',
'Nearly new Tucson with the Trend Package: panoramic sunroof, 19\" wheels, wireless charging and Hyundai SmartSense. Full factory warranty to 2028. One of the best value compact SUVs on the market today.',
'Hyundai SmartSense safety suite
Panoramic sunroof
19\" alloy wheels
Heated front seats & steering wheel
10.25\" digital cluster
Wireless charging pad
Hands-free smart liftgate
Blind-Spot View Monitor
Balance of 5-year/100,000 km warranty',
'uploads/vehicles/2023-hyundai-tucson-preferred-trend-1.jpg', 0, 10),

('2020-jeep-wrangler-unlimited-sahara', 2020, 'Jeep', 'Wrangler', 'Unlimited Sahara', 'SUV', 'used', 'available', 43900.00, NULL, 48200, 'Automatic', '4WD', 'Gasoline', '3.6L Pentastar V6', 285, '11.4 L/100 km combined', 'Firecracker Red', 'Black Cloth', '#c2231b', 4, 5, '1C4HJXEG9LW255811', 'LAH-1026',
'Four-door Sahara in Firecracker Red with the Sky One-Touch power top, LED lighting group, Cold Weather Group and the 8.4\" Uconnect. Never off-roaded, always garaged. A rare colour on a clean, well-kept Wrangler.',
'Sky One-Touch power top
LED Lighting Group
Cold Weather Group
8.4\" Uconnect with navigation
Alpine premium audio
Remote start
Blind-spot monitoring
Selec-Trac full-time 4x4
Towing capacity 1,588 kg',
'uploads/vehicles/2020-jeep-wrangler-unlimited-sahara-1.jpg', 0, 11),

('2022-volkswagen-golf-gti-autobahn', 2022, 'Volkswagen', 'Golf GTI', 'Autobahn', 'Hatchback', 'used', 'available', 39900.00, NULL, 22100, 'Dual-Clutch', 'FWD', 'Gasoline', '2.0L TSI I4', 241, '8.4 L/100 km combined', 'Moonstone Grey', 'Vienna Leather', '#8a8d92', 4, 5, '3VW6T7AU6NM019822', 'LAH-1049',
'Mk8 GTI Autobahn with the DSG, Vienna leather, Harman Kardon and IQ.DRIVE. Stock, unmodified and adult-owned. Summer and winter wheels included.',
'7-speed DSG
Autobahn trim with Vienna leather
Harman Kardon audio
IQ.DRIVE driver assistance
Adaptive chassis control (DCC)
Panoramic sunroof
Digital Cockpit Pro
Heated & ventilated front seats
Summer & winter wheel sets',
'uploads/vehicles/2022-volkswagen-golf-gti-autobahn-1.jpg', 0, 12),

('2023-kia-telluride-sx', 2023, 'Kia', 'Telluride', 'SX AWD', 'SUV', 'used', 'pending', 54900.00, NULL, 12300, 'Automatic', 'AWD', 'Gasoline', '3.8L V6', 291, '10.9 L/100 km combined', 'Everlasting Silver', 'Black Nappa Leather', '#b9bcc0', 4, 7, '5XYP5DGC6PG312477', 'LAH-1060',
'Seven-seat Telluride SX with dual sunroofs, Nappa leather, Harman Kardon and the full Kia Drive Wise suite. Only 12,300 km. A deposit has been placed — join the wait-list to be first in line if it becomes available.',
'Seven passenger seating
Dual sunroofs
Nappa leather with heated & ventilated seats
Harman Kardon 10-speaker audio
Highway Driving Assist
Blind-Spot View Monitor
12.3\" digital cluster
Smart power liftgate
Tow capacity 2,268 kg',
'uploads/vehicles/2023-kia-telluride-sx-1.jpg', 0, 13),

('2019-mazda-mx-5-gt', 2019, 'Mazda', 'MX-5', 'GT', 'Convertible', 'used', 'sold', 31900.00, NULL, 29700, 'Manual', 'RWD', 'Gasoline', '2.0L Skyactiv-G I4', 181, '7.8 L/100 km combined', 'Soul Red Crystal Metallic', 'Sport Tan Leather', '#9b1b26', 2, 2, 'JM1NDAD73K0304186', 'LAH-1019',
'Six-speed manual MX-5 GT in Soul Red with the Sport Tan interior. Sold — but I can source another. Ask me about a custom search.',
'6-speed manual
Bose audio with headrest speakers
Heated leather seats
Brembo brakes (GT-S package)
Bilstein dampers
Limited-slip differential
Adaptive LED headlights',
'uploads/vehicles/2019-mazda-mx-5-gt-1.jpg', 0, 14);

-- One row per photo; files are generated by tools/make-placeholders.php
INSERT INTO vehicle_images (vehicle_id, path, sort_order)
SELECT v.id, CONCAT('uploads/vehicles/', v.slug, '-', n.n, '.jpg'), n.n
FROM vehicles v
JOIN (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3) n
ORDER BY v.id, n.n;

-- ------------------------------------------------------------- testimonials
INSERT INTO testimonials (name, location, vehicle, rating, quote, is_published, sort_order) VALUES
('Priya S.', 'Mississauga', '2022 Audi Q5', 5, 'I had been to four dealerships before I found Alex. First one to actually answer my questions instead of asking what payment I wanted. Took the Q5 to my own mechanic, he said it was one of the cleanest he had seen. Zero pressure, all the paperwork was ready when I arrived.', 1, 1),
('Marcus T.', 'Toronto', '2022 F-150 Lariat', 5, 'Traded in my Silverado and got more than the franchise dealer offered — by a lot. The whole thing was done over text and one visit. Truck was detailed and full of gas when I picked it up.', 1, 2),
('Jennifer & Dave L.', 'Oakville', '2023 RAV4 Hybrid', 5, 'We were on a nine-month wait-list at Toyota. Alex found us a one-year-old RAV4 Hybrid with 18k on it in two days. He walked us through the CARFAX line by line. Highly recommend.', 1, 3),
('Omar K.', 'Brampton', '2021 Civic Touring', 5, 'First car purchase, credit was thin. He got me approved at a rate that was honestly better than my bank and never made me feel like a small deal. Still texts me on the anniversary of the sale.', 1, 4),
('Sandra W.', 'Vaughan', '2020 Porsche Macan S', 5, 'I know exactly what I want in a car and I do not have time to waste. Alex sourced the exact Macan spec I asked for, had it inspected by Porsche, and delivered it to my office. Professional from start to finish.', 1, 5),
('Kevin R.', 'Markham', '2022 Tesla Model 3', 5, 'Great experience buying my first EV. He showed me the battery health report before I even asked, included the home charger and set up my Tesla account in the showroom. Transparent pricing, no add-ons.', 1, 6);

-- --------------------------------------------------------------------- faqs
INSERT INTO faqs (question, answer, is_published, sort_order) VALUES
('Are you a dealership or a private seller?', 'Neither, exactly. Lucid Auto Haus is a licensed, OMVIC-registered independent consultancy. You get the legal protection and financing options of a dealership with the one-on-one service of a personal advisor. Every sale is run through a licensed dealer, so your purchase is fully protected.', 1, 1),
('Do all vehicles come with an inspection?', 'Yes. Every vehicle in my inventory is inspected by a licensed technician before it is listed, comes with a Safety Standards Certificate and a full CARFAX history report. You are welcome to take any vehicle to your own mechanic before purchase.', 1, 2),
('Can you find a specific vehicle for me?', 'Absolutely — sourcing is a big part of what I do. Tell me the year, make, model, trim, colour and budget, and I search dealer auctions, off-lease returns and private networks across Ontario and Quebec. Most requests are matched within one to three weeks.', 1, 3),
('What financing options are available?', 'I work with all major Canadian banks, credit unions and specialty lenders, so there is an option for prime, near-prime and rebuilding credit. Rates start from bank-prime on newer vehicles. A soft pre-approval takes two minutes and does not affect your credit score.', 1, 4),
('Do you accept trade-ins?', 'Yes, and I pay competitively because I retail the vehicles myself rather than sending them to auction. Send me photos and a few details and you will have a firm number within 24 hours. You can also sell me your vehicle outright without buying one.', 1, 5),
('What fees are on top of the listed price?', 'Only HST and Ontario licensing. There are no administration, documentation, etching or "market adjustment" fees. The price you see is the price you pay plus tax.', 1, 6),
('Do you deliver?', 'Complimentary delivery anywhere in the GTA. Delivery elsewhere in Ontario is available at cost, and I have shipped vehicles to buyers as far as Vancouver and Halifax.', 1, 7),
('Is there a warranty?', 'Certified vehicles carry the balance of the manufacturer warranty. Every other vehicle is eligible for an extended warranty from a national provider, and I will tell you plainly whether it is worth buying for that particular car.', 1, 8);
