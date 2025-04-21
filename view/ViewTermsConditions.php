<?php
/**
 * Terms & Conditions View
 * 
 * Displays the legal terms and conditions for the BikeStores website:
 * - Legal information and site usage rules
 * - Privacy policy and data collection details
 * - Employee login terms
 * - Intellectual property rights
 * - Liability limitations
 * - Contact information
 * 
 * @package BikeStore\Views
 * @version 1.0
 * @lastModified Generated dynamically from file modification time
 */

/**
 * Include header template
 */
include_once('www/header.inc.php');

/**
 * Main content section
 * Structured in multiple sections covering different legal aspects:
 * 1. Legal Information
 * 2. Access and Use
 * 3. Employee Login and Cookies
 * 4. Intellectual Property
 * 5. Privacy Policy
 * 6. Liability
 * 7. External Links
 * 8. Terms Updates
 * 9. Governing Law
 * 10. Contact Information
 * 
 * @var string $lastUpdated Last modification date of this file
 */
$lastUpdated = date("F d, Y", filemtime(__FILE__));
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h1 class="text-center mb-4">Terms & Conditions</h1>
                    <p class="text-muted text-center mb-4">Last Updated: <?php echo $lastUpdated; ?></p>

                    <div class="alert alert-light border mb-4">
                        <p>Welcome to BikeStores! These Terms of Use govern your access to and use of our <a href="/SAE401/home" class="text-success">website</a>. By accessing or using the Site, you agree to comply with these Terms. If you do not agree with these Terms, please do not use the Site.</p>
                    </div>

                    <div class="mb-4">
                        <h3 class="text-success mb-3">1. Legal Information</h3>
                        <p>BikeStores is a bicycle retail business with multiple locations across the United States. Our Site serves as an online catalog of our products and services, intended solely for informational purposes. The Site is not a commercial transaction platform unless specifically stated.</p>
                    </div>

                    <div class="mb-4">
                        <h3 class="text-success mb-3">2. Access and Use of the Site</h3>
                        <p>The Site is available to users of all ages; however, certain features, such as employee logins, are restricted to authorized personnel only.</p>
                        <p>You are permitted to browse the Site and access product information without creating an account. However, you agree not to misuse the Site or access areas that are restricted to employees without authorization. Unauthorized use of the Site, including attempts to gain unauthorized access to restricted areas, may result in termination of your access.</p>
                        <p>You agree not to use the Site for any unlawful or unauthorized purposes, including, but not limited to:</p>
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item bg-light">Engaging in fraudulent activity</li>
                            <li class="list-group-item bg-light">Posting, transmitting, or otherwise distributing harmful, offensive, or illegal content</li>
                            <li class="list-group-item bg-light">Interfering with or disrupting the operation of the Site or servers connected to the Site</li>
                            <li class="list-group-item bg-light">Any unauthorized use may result in the termination of access and possible legal action</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h3 class="text-success mb-3">3. Employee Login and Cookies</h3>
                        <p>The Site includes a login feature exclusively for BikeStores employees. Unauthorized login attempts are prohibited.</p>
                        <p>We use cookies to Improve site functionality, Support employee login sessions.</p>
                        <p>By continuing to use the Site, you consent to our use of cookies. You can manage cookie preferences through your browser settings.</p>

                    <div class="mb-4">
                        <h3 class="text-success mb-3">4. Intellectual Property Rights</h3>
                        <p>All content on the Site, including text, images, logos, trademarks, graphics, and other materials, is the intellectual property of BikeStores or its licensors. You may not use, copy, distribute, or modify any content without prior written consent from BikeStores.</p>
                    </div>

                    <div class="mb-4">
                        <h3 class="text-success mb-3">5. Privacy Policy</h3>
                        <p>We collect the following types of information:</p>
                        <ul>
                            <label>Information You Provide:</label>
                            <li>Name, email address, phone number, or any personal data submitted through forms or email.</li> <br>

                            <label>Automatically Collected Information:</label>
                            <li>IP address, browser type, pages visited, time spent, and device data collected via cookies or analytics tools.</li><br>

                            <label>Employee Login Data:</label>
                            <li>Authentication credentials and session data used for managing secure employee access.</li>
                        </ul>
                        <p>How We Use Your Data</p>
                        <p>We use your information to Improve user experience, Maintain security and website performance, Comply with legal obligations. We do not sell your data.</p>
                    </div>

                    <div class="mb-4">
                        <h3 class="text-success mb-3">6. Limitation of Liability</h3>
                        <p>The Site is provided "as is" and without warranties of any kind, either express or implied. While we strive to provide accurate information, we do not guarantee the completeness, accuracy, or reliability of the product descriptions, pricing, or availability.</p>
                        <p>In no event shall BikeStores be liable for any direct, indirect, incidental, special, consequential, or punitive damages arising out of the use or inability to use the Site, including, but not limited to, damages for loss of profits, goodwill, use, or data.</p>
                    </div>

                    <div class="mb-4">
                        <h3 class="text-success mb-3">7. External Links</h3>
                        <p>The Site may contain links to third-party websites or resources. We do not control and are not responsible for the content, privacy practices, or policies of these third-party sites. Accessing these sites is at your own risk.</p>
                    </div>

                    <div class="mb-4">
                        <h3 class="text-success mb-3">8. Changes to These Terms</h3>
                        <p>BikeStores reserves the right to modify or update these Terms at any time. Any changes will be effective upon posting on the Site. It is your responsibility to review these Terms periodically to stay informed of any updates. Your continued use of the Site following the posting of revised Terms constitutes your acceptance of those changes.</p>
                    </div>

                    <div class="mb-4">
                        <h3 class="text-success mb-3">9. Governing Law and Dispute Resolution</h3>
                        <p>These Terms are governed by the laws of the State of California, without regard to its conflict of laws principles. Any disputes arising out of or relating to the use of the Site shall be resolved through binding arbitration in accordance with the rules of the American Arbitration Association.</p>
                    </div>

                    <div class="mb-4">
                        <h3 class="text-success mb-3">10. Contact Information</h3>
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <p class="mb-1">If you have any questions or concerns about these Terms, please contact us at:</p>
                                <p class="mb-1"><strong>BikeStores Headquarters</strong></p>
                                <p class="mb-1">1234 Bicycle Lane</p>
                                <p class="mb-1">Cycling City, CA 90001</p>
                                <p class="mb-1">Email: <a href="mailto:support@bikestores.com" class="text-success">support@bikestores.com</a></p>
                                <p class="mb-0">Phone: (555) 123-4567</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success mt-5">
                        <h5 class="mb-3">Key Legal Considerations:</h5>
                        <ul class="mb-0">
                            <li><strong>Privacy Policy Reference:</strong> A direct mention of your Privacy Policy is integrated into the document, ensuring users are aware of the data collection and usage practices.</li>
                            <li><strong>Limitation of Liability:</strong> A typical clause that limits your liability for damages arising from using the site.</li>
                            <li><strong>Governing Law & Dispute Resolution:</strong> Specifies that any legal disputes will be resolved under California law, which is a common practice.</li>
                            <li><strong>Modifications to Terms:</strong> Clearly states your right to update the terms and the process for users to stay informed.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Include footer template
 */
include_once('www/footer.inc.php');
?>