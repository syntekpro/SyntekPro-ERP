<?php

namespace App\Services\Zatca;

use App\Models\Sale;
use App\Services\Settings\BusinessSettingsService;
use DOMDocument;
use DOMElement;

/**
 * Builds a ZATCA-compliant UBL 2.1 invoice XML document for a Sale.
 *
 * Scope note: this produces the invoice business content (parties, lines,
 * totals, PIH/ICV extension references). It does NOT yet:
 *   - embed the digital signature / cryptographic stamp (needs a CSID)
 *   - embed the QR code as a Base64 node inside UBLExtensions (same reason)
 *   - distinguish Standard vs Simplified party requirements beyond the
 *     InvoiceTypeCode subtype
 *
 * Those steps slot in once ZatcaCsrGenerator + ZatcaApiClient have produced
 * a real compliance/production CSID. Until then this XML is useful for
 * structural review and for the compliance sandbox's *unsigned* checks,
 * but must not be treated as a submittable production invoice.
 */
class UblInvoiceBuilder
{
    public function __construct(private readonly BusinessSettingsService $businessSettings)
    {
    }

    public function build(Sale $sale, string $previousInvoiceHash): string
    {
        $settings = $this->businessSettings->current();
        $shop = $sale->shop;

        $sellerName = $shop?->legal_name ?: $settings->legal_name ?: config('app.name', 'ERP');
        $vatNumber = $shop?->vat_registration_number ?: $settings->vat_number ?: '000000000000000';

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $invoice = $doc->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:Invoice-2', 'Invoice');
        $invoice->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:cac',
            'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2'
        );
        $invoice->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:cbc',
            'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2'
        );
        $doc->appendChild($invoice);

        $this->appendText($doc, $invoice, 'cbc:ProfileID', 'reporting:1.0');
        $this->appendText($doc, $invoice, 'cbc:ID', (string) $sale->invoice_number);
        $this->appendText($doc, $invoice, 'cbc:UUID', (string) $sale->invoice_uuid);
        $this->appendText($doc, $invoice, 'cbc:IssueDate', $sale->sold_at?->format('Y-m-d') ?? now()->format('Y-m-d'));
        $this->appendText($doc, $invoice, 'cbc:IssueTime', $sale->sold_at?->format('H:i:s') ?? now()->format('H:i:s'));

        $typeCodeEl = $this->appendText($doc, $invoice, 'cbc:InvoiceTypeCode', '388');
        $typeCodeEl->setAttribute('name', $sale->zatca_invoice_type_code ?? '0200000');

        $this->appendText($doc, $invoice, 'cbc:DocumentCurrencyCode', $settings->currency_code ?: 'SAR');
        $this->appendText($doc, $invoice, 'cbc:TaxCurrencyCode', $settings->currency_code ?: 'SAR');

        // PIH - previous invoice hash. Real signing wraps this in
        // cac:AdditionalDocumentReference (ID = PIH) with a base64 embedded
        // document; kept as a plain reference node here pending signing.
        $pihRef = $doc->createElement('cac:AdditionalDocumentReference');
        $this->appendText($doc, $pihRef, 'cbc:ID', 'PIH');
        $attachment = $doc->createElement('cac:Attachment');
        $embedded = $this->appendText($doc, $attachment, 'cbc:EmbeddedDocumentBinaryObject', $previousInvoiceHash);
        $embedded->setAttribute('mimeCode', 'text/plain');
        $pihRef->appendChild($attachment);
        $invoice->appendChild($pihRef);

        $invoice->appendChild($this->buildParty(
            $doc,
            'cac:AccountingSupplierParty',
            $sellerName,
            $vatNumber,
            $settings
        ));

        if ($sale->customer !== null) {
            $invoice->appendChild($this->buildParty(
                $doc,
                'cac:AccountingCustomerParty',
                $sale->customer->name ?? 'Customer',
                $sale->customer->vat_number ?? null,
                $settings
            ));
        }

        $taxTotal = $doc->createElement('cac:TaxTotal');
        $this->appendText($doc, $taxTotal, 'cbc:TaxAmount', number_format((float) $sale->vat_total, 2, '.', ''))
            ->setAttribute('currencyID', $settings->currency_code ?: 'SAR');
        $invoice->appendChild($taxTotal);

        $legalTotal = $doc->createElement('cac:LegalMonetaryTotal');
        $this->appendMonetary($doc, $legalTotal, 'cbc:LineExtensionAmount', $sale->subtotal, $settings);
        $this->appendMonetary($doc, $legalTotal, 'cbc:TaxExclusiveAmount', $sale->subtotal, $settings);
        $this->appendMonetary($doc, $legalTotal, 'cbc:TaxInclusiveAmount', $sale->total, $settings);
        $this->appendMonetary($doc, $legalTotal, 'cbc:PayableAmount', $sale->total, $settings);
        $invoice->appendChild($legalTotal);

        foreach ($sale->items as $index => $item) {
            $invoice->appendChild($this->buildInvoiceLine($doc, $item, $index + 1, $settings));
        }

        return $doc->saveXML();
    }

    private function buildParty(
        DOMDocument $doc,
        string $wrapperTag,
        string $name,
        ?string $vatNumber,
        object $settings
    ): DOMElement {
        $wrapper = $doc->createElement($wrapperTag);
        $party = $doc->createElement('cac:Party');

        if ($vatNumber !== null) {
            $taxScheme = $doc->createElement('cac:PartyTaxScheme');
            $this->appendText($doc, $taxScheme, 'cbc:CompanyID', $vatNumber);
            $schemeEl = $doc->createElement('cac:TaxScheme');
            $this->appendText($doc, $schemeEl, 'cbc:ID', 'VAT');
            $taxScheme->appendChild($schemeEl);
            $party->appendChild($taxScheme);
        }

        $address = $doc->createElement('cac:PostalAddress');
        $this->appendText($doc, $address, 'cbc:StreetName', $settings->street_name ?? '');
        $this->appendText($doc, $address, 'cbc:BuildingNumber', $settings->building_number ?? '');
        $this->appendText($doc, $address, 'cbc:CitySubdivisionName', $settings->district ?? '');
        $this->appendText($doc, $address, 'cbc:CityName', $settings->city ?? '');
        $this->appendText($doc, $address, 'cbc:PostalZone', $settings->postal_code ?? '');
        $country = $doc->createElement('cac:Country');
        $this->appendText($doc, $country, 'cbc:IdentificationCode', $settings->country_code ?? 'SA');
        $address->appendChild($country);
        $party->appendChild($address);

        $legalEntity = $doc->createElement('cac:PartyLegalEntity');
        $this->appendText($doc, $legalEntity, 'cbc:RegistrationName', $name);
        $party->appendChild($legalEntity);

        $wrapper->appendChild($party);

        return $wrapper;
    }

    private function buildInvoiceLine(DOMDocument $doc, object $item, int $lineNumber, object $settings): DOMElement
    {
        $line = $doc->createElement('cac:InvoiceLine');
        $this->appendText($doc, $line, 'cbc:ID', (string) $lineNumber);

        $qty = $this->appendText($doc, $line, 'cbc:InvoicedQuantity', (string) $item->quantity);
        $qty->setAttribute('unitCode', 'PCE');

        $this->appendMonetary($doc, $line, 'cbc:LineExtensionAmount', $item->line_total, $settings);

        $taxTotal = $doc->createElement('cac:TaxTotal');
        $this->appendMonetary($doc, $taxTotal, 'cbc:TaxAmount', $item->vat_amount, $settings);
        $line->appendChild($taxTotal);

        $itemEl = $doc->createElement('cac:Item');
        $this->appendText($doc, $itemEl, 'cbc:Name', $item->product_name ?? '');
        $classifiedTax = $doc->createElement('cac:ClassifiedTaxCategory');
        $this->appendText($doc, $classifiedTax, 'cbc:Percent', number_format((float) $item->vat_rate, 2, '.', ''));
        $scheme = $doc->createElement('cac:TaxScheme');
        $this->appendText($doc, $scheme, 'cbc:ID', 'VAT');
        $classifiedTax->appendChild($scheme);
        $itemEl->appendChild($classifiedTax);
        $line->appendChild($itemEl);

        $price = $doc->createElement('cac:Price');
        $this->appendMonetary($doc, $price, 'cbc:PriceAmount', $item->unit_price, $settings);
        $line->appendChild($price);

        return $line;
    }

    private function appendMonetary(DOMDocument $doc, DOMElement $parent, string $tag, mixed $amount, object $settings): DOMElement
    {
        $el = $this->appendText($doc, $parent, $tag, number_format((float) $amount, 2, '.', ''));
        $el->setAttribute('currencyID', $settings->currency_code ?: 'SAR');

        return $el;
    }

    private function appendText(DOMDocument $doc, DOMElement $parent, string $tag, string $value): DOMElement
    {
        $el = $doc->createElement($tag, htmlspecialchars($value, ENT_XML1));
        $parent->appendChild($el);

        return $el;
    }
}
