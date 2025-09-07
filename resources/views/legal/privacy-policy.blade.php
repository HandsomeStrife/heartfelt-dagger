<x-layouts.app>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 py-12">
        <div class="container mx-auto px-4 max-w-4xl">
            <div class="bg-slate-900/80 backdrop-blur-xl rounded-3xl border border-slate-700/50 p-8 md:p-12">
                <div class="text-center mb-12">
                    <h1 class="text-4xl md:text-5xl font-bold text-white mb-4 font-outfit">
                        Privacy Policy
                    </h1>
                    <p class="text-slate-300 text-lg">
                        Last updated: {{ date('F j, Y') }}
                    </p>
                </div>

                <div class="prose prose-invert prose-slate max-w-none">
                    <div class="space-y-8">
                        <section>
                            <h2 class="text-2xl font-bold text-white mb-4 font-outfit">1. Introduction</h2>
                            <p class="text-slate-300 leading-relaxed mb-4">
                                HeartfeltDagger ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our DaggerHeart TTRPG companion service.
                            </p>
                            <p class="text-slate-300 leading-relaxed">
                                By using our Service, you consent to the data practices described in this policy.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-white mb-4 font-outfit">2. Information We Collect</h2>
                            
                            <h3 class="text-xl font-semibold text-white mb-3 font-outfit">2.1 Information You Provide</h3>
                            <p class="text-slate-300 leading-relaxed mb-4">
                                When you create an account or use our Service, we may collect:
                            </p>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4 mb-6">
                                <li><strong>Account Information:</strong> Username, email address, and encrypted password</li>
                                <li><strong>Character Data:</strong> DaggerHeart character builds, stats, equipment, and customizations</li>
                                <li><strong>Campaign Content:</strong> Campaign names, descriptions, and associated data</li>
                                <li><strong>Room Participation:</strong> Room names, participant lists, and session notes</li>
                                <li><strong>Storage Account Credentials:</strong> Encrypted OAuth tokens for connected cloud storage services</li>
                            </ul>

                            <h3 class="text-xl font-semibold text-white mb-3 font-outfit">2.2 Automatically Collected Information</h3>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4 mb-6">
                                <li><strong>Usage Data:</strong> Pages visited, features used, and interaction patterns</li>
                                <li><strong>Device Information:</strong> Browser type, operating system, and device identifiers</li>
                                <li><strong>Log Data:</strong> IP addresses, access times, and error logs for service improvement</li>
                                <li><strong>Session Data:</strong> Login sessions and authentication tokens</li>
                            </ul>

                            <h3 class="text-xl font-semibold text-white mb-3 font-outfit">2.3 Third-Party Integration Data</h3>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                                <li><strong>Google Drive Integration:</strong> OAuth access and refresh tokens, file metadata</li>
                                <li><strong>Wasabi Storage:</strong> Access credentials and upload session information</li>
                                <li><strong>WebRTC Communications:</strong> Temporary signaling data for peer-to-peer connections</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-white mb-4 font-outfit">3. How We Use Your Information</h2>
                            <p class="text-slate-300 leading-relaxed mb-4">
                                We use the collected information for the following purposes:
                            </p>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                                <li><strong>Service Provision:</strong> To provide and maintain our DaggerHeart companion features</li>
                                <li><strong>Account Management:</strong> To create, authenticate, and manage user accounts</li>
                                <li><strong>Character Storage:</strong> To save and sync character data across devices and sessions</li>
                                <li><strong>Cloud Integration:</strong> To facilitate direct uploads to your connected storage accounts</li>
                                <li><strong>Communication:</strong> To enable real-time video conferencing and messaging in rooms</li>
                                <li><strong>Service Improvement:</strong> To analyze usage patterns and improve our features</li>
                                <li><strong>Security:</strong> To detect and prevent fraud, abuse, and security incidents</li>
                                <li><strong>Legal Compliance:</strong> To comply with applicable laws and regulations</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-white mb-4 font-outfit">4. Google Drive Integration</h2>
                            <p class="text-slate-300 leading-relaxed mb-4">
                                Our Google Drive integration allows you to upload video recordings directly to your own Google Drive account. Here's how it works:
                            </p>
                            
                            <h3 class="text-xl font-semibold text-white mb-3 font-outfit">4.1 OAuth Authorization</h3>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4 mb-6">
                                <li>We use Google's OAuth 2.0 system to securely access your Google Drive</li>
                                <li>You explicitly authorize our application to upload files to your Drive</li>
                                <li>We store encrypted access and refresh tokens to maintain the connection</li>
                                <li>You can revoke access at any time through your Google Account settings</li>
                            </ul>

                            <h3 class="text-xl font-semibold text-white mb-3 font-outfit">4.2 Data Handling</h3>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4 mb-6">
                                <li><strong>Direct Upload:</strong> Video recordings are uploaded directly from your browser to Google Drive</li>
                                <li><strong>No Server Storage:</strong> We do not store copies of your recordings on our servers</li>
                                <li><strong>Metadata Only:</strong> We only store file metadata (name, size, upload time) for display purposes</li>
                                <li><strong>Your Ownership:</strong> All uploaded content remains in your Google Drive under your control</li>
                            </ul>

                            <h3 class="text-xl font-semibold text-white mb-3 font-outfit">4.3 Permissions Requested</h3>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                                <li><strong>File Creation:</strong> Permission to create new files in your Google Drive</li>
                                <li><strong>File Management:</strong> Limited access to files created by our application</li>
                                <li><strong>Account Information:</strong> Basic profile information for account verification</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-white mb-4 font-outfit">5. Video Recording and Transcription</h2>
                            
                            <h3 class="text-xl font-semibold text-white mb-3 font-outfit">5.1 Recording Consent</h3>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4 mb-6">
                                <li>All video recording requires explicit consent from participants</li>
                                <li>Consent status is stored temporarily during active sessions</li>
                                <li>Participants can withdraw consent and leave recorded sessions at any time</li>
                                <li>Room creators are responsible for managing recording permissions</li>
                            </ul>

                            <h3 class="text-xl font-semibold text-white mb-3 font-outfit">5.2 Speech-to-Text Processing</h3>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4 mb-6">
                                <li><strong>Browser-based STT:</strong> Processed locally on your device when using browser speech recognition</li>
                                <li><strong>AssemblyAI Integration:</strong> Audio may be sent to AssemblyAI for transcription when enabled</li>
                                <li><strong>Temporary Processing:</strong> Audio data is processed in real-time and not permanently stored</li>
                                <li><strong>Transcript Storage:</strong> Generated transcripts are saved as session notes in our database</li>
                            </ul>

                            <h3 class="text-xl font-semibold text-white mb-3 font-outfit">5.3 Recording Storage</h3>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                                <li>Video recordings are uploaded directly to the room creator's designated storage account</li>
                                <li>We do not retain copies of video content on our servers</li>
                                <li>Recording metadata (filename, size, timestamp) is stored for organizational purposes</li>
                                <li>Access to recordings is controlled by the storage account owner</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-white mb-4 font-outfit">6. Data Sharing and Disclosure</h2>
                            <p class="text-slate-300 leading-relaxed mb-4">
                                We do not sell, trade, or otherwise transfer your personal information to third parties except in the following circumstances:
                            </p>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                                <li><strong>Service Providers:</strong> Third-party services that help us operate our platform (hosting, analytics)</li>
                                <li><strong>Legal Requirements:</strong> When required by law, court order, or government request</li>
                                <li><strong>Safety and Security:</strong> To protect our rights, property, or safety, or that of our users</li>
                                <li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets</li>
                                <li><strong>Consent:</strong> When you have given explicit consent for specific sharing</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-white mb-4 font-outfit">7. Data Security</h2>
                            <p class="text-slate-300 leading-relaxed mb-4">
                                We implement appropriate security measures to protect your information:
                            </p>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                                <li><strong>Encryption:</strong> Sensitive data is encrypted both in transit and at rest</li>
                                <li><strong>Access Controls:</strong> Limited access to personal data on a need-to-know basis</li>
                                <li><strong>Secure Authentication:</strong> Password hashing and secure session management</li>
                                <li><strong>Regular Updates:</strong> Security patches and system updates are applied promptly</li>
                                <li><strong>Monitoring:</strong> Continuous monitoring for security threats and vulnerabilities</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-white mb-4 font-outfit">8. Data Retention</h2>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                                <li><strong>Account Data:</strong> Retained while your account is active and for a reasonable period after deletion</li>
                                <li><strong>Character Data:</strong> Stored indefinitely unless you delete characters or your account</li>
                                <li><strong>Session Logs:</strong> Retained for 90 days for security and debugging purposes</li>
                                <li><strong>OAuth Tokens:</strong> Stored until you disconnect the integration or delete your account</li>
                                <li><strong>Video Recordings:</strong> Not stored on our servers; retention controlled by your storage provider</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-white mb-4 font-outfit">9. Your Rights and Choices</h2>
                            <p class="text-slate-300 leading-relaxed mb-4">
                                You have the following rights regarding your personal information:
                            </p>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                                <li><strong>Access:</strong> Request access to your personal data</li>
                                <li><strong>Correction:</strong> Update or correct inaccurate information</li>
                                <li><strong>Deletion:</strong> Request deletion of your account and associated data</li>
                                <li><strong>Portability:</strong> Export your character and campaign data</li>
                                <li><strong>Opt-out:</strong> Disable certain features like speech-to-text or recording</li>
                                <li><strong>Revoke Consent:</strong> Disconnect third-party integrations at any time</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-white mb-4 font-outfit">10. Cookies and Tracking</h2>
                            <p class="text-slate-300 leading-relaxed mb-4">
                                We use cookies and similar technologies to:
                            </p>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                                <li>Maintain your login session</li>
                                <li>Remember your preferences and settings</li>
                                <li>Analyze usage patterns and improve our service</li>
                                <li>Provide security features and fraud prevention</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-white mb-4 font-outfit">11. Children's Privacy</h2>
                            <p class="text-slate-300 leading-relaxed">
                                Our Service is not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13. If you are a parent or guardian and believe your child has provided us with personal information, please contact us so we can delete such information.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-white mb-4 font-outfit">12. International Data Transfers</h2>
                            <p class="text-slate-300 leading-relaxed">
                                Your information may be transferred to and processed in countries other than your own. We ensure that such transfers comply with applicable data protection laws and that appropriate safeguards are in place to protect your information.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-white mb-4 font-outfit">13. Changes to This Privacy Policy</h2>
                            <p class="text-slate-300 leading-relaxed">
                                We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date. You are advised to review this Privacy Policy periodically for any changes.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-white mb-4 font-outfit">14. Contact Us</h2>
                            <p class="text-slate-300 leading-relaxed mb-4">
                                If you have any questions about this Privacy Policy or our data practices, please contact us:
                            </p>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                                <li>Through our Discord community (link in footer)</li>
                                <li>Via our GitHub repository issues page</li>
                                <li>By creating an issue in our open source project</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-2xl font-bold text-white mb-4 font-outfit">15. Third-Party Services</h2>
                            <p class="text-slate-300 leading-relaxed mb-4">
                                Our Service integrates with third-party services that have their own privacy policies:
                            </p>
                            <ul class="list-disc list-inside text-slate-300 space-y-2 ml-4">
                                <li><strong>Google Drive:</strong> <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300 underline">Google Privacy Policy</a></li>
                                <li><strong>Wasabi Storage:</strong> <a href="https://wasabi.com/privacy-policy/" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300 underline">Wasabi Privacy Policy</a></li>
                                <li><strong>AssemblyAI:</strong> <a href="https://www.assemblyai.com/privacy-policy" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300 underline">AssemblyAI Privacy Policy</a></li>
                                <li><strong>Ably (WebRTC signaling):</strong> <a href="https://ably.com/privacy" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300 underline">Ably Privacy Policy</a></li>
                            </ul>
                        </section>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-slate-700">
                    <div class="text-center">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold rounded-xl hover:from-amber-600 hover:to-orange-600 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
