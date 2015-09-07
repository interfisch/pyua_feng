<?xml version="1.0" encoding="UTF-8"?> 
<!-- magento <-> temp -->
     
<xsl:stylesheet 
	version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns="http://www.w3.org/TR/xhtml1/strict"> 

	<xsl:output method="xml" version="4.0" indent="yes" encoding="UTF-8" doctype-system="Documents.dtd" media-type="text/xml"/>

   <xsl:template match="/">      
      	<ORDER>
			<xsl:for-each select="ORDER_LIST/ORDER">
			<ORDER_INFO>	
				<DO_SQL_BEFORE></DO_SQL_BEFORE>
				<DO_SQL_AFTER></DO_SQL_AFTER>
				<ORDER_HEADER>					
					<!-- WAWI INFOS -->
					<!-- Debitoren Kontoo Interessentenvorlage z.b. D600000 -->
					<DEB_ACCOUNT>D1000000</DEB_ACCOUNT>
					<ORDER_ID>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_ID"/>
					</ORDER_ID>
					<ORDER_CID>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_CID"/>
					</ORDER_CID>
					<INVOICE_ID><xsl:value-of select="ORDER_HEADER/ORDER_INFO/INVOICE_ID"/></INVOICE_ID>
					<INVOICE_CID><xsl:value-of select="ORDER_HEADER/ORDER_INFO/INVOICE_CID"/></INVOICE_CID>
					<ORDER_STATUS>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_STATUS"/>
					</ORDER_STATUS>
					<ORDER_CODE>WBST</ORDER_CODE>
					<ORDER_TYPE>Web-Bestellung</ORDER_TYPE>
					<ORDER_STATUS>1</ORDER_STATUS>
					<ORDER_TEXT1></ORDER_TEXT1>
					<ORDER_TEXT2></ORDER_TEXT2>
					<xsl:choose>
						<!-- Als Gast im Shop, dann Kundennummer = Order ID -->
              				<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/PARTY_ID = '0'">
              					<CUSTOMER_ID><xsl:value-of select="2000000+ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/ADDRESS_ID"/></CUSTOMER_ID>
							</xsl:when>
							<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/PARTY_ID = ''">
              					<CUSTOMER_ID><xsl:value-of select="2000000+ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/ADDRESS_ID"/></CUSTOMER_ID>
							</xsl:when>
            				<!-- Standart: Als Kunde im Shop -->
            				<xsl:otherwise>
								<CUSTOMER_ID>
									<xsl:text></xsl:text><xsl:value-of select="2000000+ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/PARTY_ID"/>
								</CUSTOMER_ID>
              				</xsl:otherwise>
          				</xsl:choose>	
					<CUSTOMER_CID></CUSTOMER_CID>
					<xsl:choose>
              			<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/COUNTRY = 'DE'">
              				<CUSTOMER_LANGUAGE>D</CUSTOMER_LANGUAGE>
						</xsl:when>
						<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/COUNTRY = 'AT'">
              				<CUSTOMER_LANGUAGE>D</CUSTOMER_LANGUAGE>
						</xsl:when>
						<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/COUNTRY = 'IT'">
              				<CUSTOMER_LANGUAGE>IT</CUSTOMER_LANGUAGE>
						</xsl:when>
						<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/COUNTRY = 'ES'">
              				<CUSTOMER_LANGUAGE>ES</CUSTOMER_LANGUAGE>
						</xsl:when>
            			<!-- Standard Sprache-->
            			<xsl:otherwise>
							<CUSTOMER_LANGUAGE>EN</CUSTOMER_LANGUAGE>	
              			</xsl:otherwise>
          			</xsl:choose>
					<CUSTOMER_GROUP>Endkunde</CUSTOMER_GROUP>
					<CUSTOMER_PRICE_GROUP>1</CUSTOMER_PRICE_GROUP>
					<ACTUAL_DATE>
						<xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 9, 2)" /><xsl:text>.</xsl:text>
			            <xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 6, 2)" /><xsl:text>.</xsl:text>
			            <xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 1, 4)" />  
					</ACTUAL_DATE>
					<ORDERS_DATE>
						<xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 9, 2)" /><xsl:text>.</xsl:text>
			            <xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 6, 2)" /><xsl:text>.</xsl:text>
			            <xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 1, 4)" />  
					</ORDERS_DATE>
					<ORDERS_YEAR>
						<xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 1, 4)" />  
					</ORDERS_YEAR>
					<ORDERS_MONTH>
			            <xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 6, 2)" />
					</ORDERS_MONTH>
					<ORDERS_YYYYMMDD>
						<xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 1, 4)" />
						<xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 6, 2)" />
						<xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 9, 2)" />  
					</ORDERS_YYYYMMDD>
					<ORDERS_PERIODE>
						<xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 1, 4)" /><xsl:text>0</xsl:text>
						<xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 6, 2)" />
					</ORDERS_PERIODE>
					<!-- Kalenderwoche -->
					<ORDER_DATE_KW>
						<xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 1, 4)" />
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_DATE_KW" />
					</ORDER_DATE_KW>
					<ORDER_IP>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_IP"/>
					</ORDER_IP>
					<ORDER_CURRENCY>
						<xsl:value-of select="ORDER_SUMMARY/ORDER_CURRENCY_CODE"/>
					</ORDER_CURRENCY>
					<ORDER_CURRENCY_RATE>1.000000</ORDER_CURRENCY_RATE>
					<TOTAL_ITEM_NUM>
						<xsl:value-of select="translate(ORDER_SUMMARY/TOTAL_ITEM_NUM, '.', ',')"/>
					</TOTAL_ITEM_NUM>
					<!-- Subtotal is without shipping -->
					<ORDER_SUBTOTAL_NET>
						<xsl:value-of select="translate(ORDER_SUMMARY/SUBTOTAL_AMOUNT_NET, '.', ',')"/>
					</ORDER_SUBTOTAL_NET>
					<ORDER_TOTAL_NET>
						<xsl:value-of select="translate(ORDER_SUMMARY/TOTAL_AMOUNT_NET, '.', ',')"/>
					</ORDER_TOTAL_NET>
					<ORDER_TOTAL_DISCOUNT_NET>
						<xsl:value-of select="translate(ORDER_SUMMARY/DISCOUNT_TOTAL_AMOUNT_NET, '.', ',')"/>
					</ORDER_TOTAL_DISCOUNT_NET>
					<!-- Subtotal is without shipping -->
					<!-- Unterscheidung zwischen Brutto und netto Kunden -->
					<xsl:choose>
              			<xsl:when test="ORDER_HEADER/ORDER_INFO/CUSTOMER_NET = '1'">
              						<ORDER_SUBTOTAL_GROS>
												<xsl:value-of select="translate(ORDER_SUMMARY/SUBTOTAL_AMOUNT_NET, '.', ',')"/>
									</ORDER_SUBTOTAL_GROS>
									<ORDER_TOTAL_GROS>
										<xsl:value-of select="translate(ORDER_SUMMARY/TOTAL_AMOUNT_NET, '.', ',')"/>
									</ORDER_TOTAL_GROS>
									<ORDER_TOTAL_DISCOUNT_GROS>
										<xsl:value-of select="translate(ORDER_SUMMARY/DISCOUNT_TOTAL_AMOUNT_NET, '.', ',')"/>
									</ORDER_TOTAL_DISCOUNT_GROS>
									<ORDER_TOTAL_DISCOUNT_TAX_AMOUNT>0</ORDER_TOTAL_DISCOUNT_TAX_AMOUNT>
									<ORDER_TOTAL_TAX>0</ORDER_TOTAL_TAX>
									<ORDER_TOTAL_TAX_RATE></ORDER_TOTAL_TAX_RATE>
									<ORDER_TOTAL_TAX_FLAG></ORDER_TOTAL_TAX_FLAG>
						</xsl:when>
						<!-- Brutto Kunde -->
            			<xsl:otherwise>
									<ORDER_SUBTOTAL_GROS>
												<xsl:value-of select="translate(ORDER_SUMMARY/SUBTOTAL_AMOUNT, '.', ',')"/>
									</ORDER_SUBTOTAL_GROS>
									<ORDER_TOTAL_GROS>
										<xsl:value-of select="translate(ORDER_SUMMARY/TOTAL_AMOUNT, '.', ',')"/>
									</ORDER_TOTAL_GROS>
									<ORDER_TOTAL_DISCOUNT_GROS>
										<xsl:value-of select="translate(ORDER_SUMMARY/DISCOUNT_TOTAL_AMOUNT, '.', ',')"/>
									</ORDER_TOTAL_DISCOUNT_GROS>
									<ORDER_TOTAL_DISCOUNT_TAX_AMOUNT>
										<xsl:value-of select="translate(ORDER_SUMMARY/DISCOUNT_TOTAL_AMOUNT_TAX, '.', ',')"/>
									</ORDER_TOTAL_DISCOUNT_TAX_AMOUNT>
									<ORDER_TOTAL_TAX>
										<xsl:value-of select="translate(ORDER_SUMMARY/TOTAL_TAX_AMOUNT, '.', ',')"/>
									</ORDER_TOTAL_TAX>
									<ORDER_TOTAL_TAX_RATE></ORDER_TOTAL_TAX_RATE>
									<ORDER_TOTAL_TAX_FLAG></ORDER_TOTAL_TAX_FLAG>
              			</xsl:otherwise>
          			</xsl:choose>
					<!-- lieferdatum -->
					<DELIVERY_DATE>
						<xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 9, 2)" /><xsl:text>.</xsl:text>
			 		    <xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 6, 2)" /><xsl:text>.</xsl:text>
			            <xsl:value-of select="substring(ORDER_HEADER/ORDER_INFO/ORDER_DATE, 1, 4)" />
					</DELIVERY_DATE>	
					<!-- Zahlungsarten -->
					<xsl:choose>
						<!-- Kreditkarte -->
              			<xsl:when test="contains(ORDER_HEADER/ORDER_INFO/PAYMENT/PAYMENT_TERM, 'credit')">
              				<PAYMENTMETHOD>Kreditkarte</PAYMENTMETHOD>	
							<PAYMENT_METHOD_ID>23</PAYMENT_METHOD_ID>
						</xsl:when>
						<!-- Kreditkarte -->
						<xsl:when test="contains(ORDER_HEADER/ORDER_INFO/PAYMENT/PAYMENT_TERM, 'cc')">
              				<PAYMENTMETHOD>Kreditkarte</PAYMENTMETHOD>	
							<PAYMENT_METHOD_ID>23</PAYMENT_METHOD_ID>
						</xsl:when>
						<!-- Rechnung -->
						<xsl:when test="contains(ORDER_HEADER/ORDER_INFO/PAYMENT/PAYMENT_TERM, 'invoice')">
              				<PAYMENTMETHOD>Rechnung</PAYMENTMETHOD>	
							<PAYMENT_METHOD_ID>35</PAYMENT_METHOD_ID>
						</xsl:when>
						<!-- Paypal etc -->
						<xsl:when test="contains(ORDER_HEADER/ORDER_INFO/PAYMENT/PAYMENT_TERM, 'paypal')">
              				<PAYMENTMETHOD>Paypal</PAYMENTMETHOD>	
							<PAYMENT_METHOD_ID>24</PAYMENT_METHOD_ID>
						</xsl:when>
						<!-- Standart: Onlineüberseisung (Sofortüberweisung, giropay) Paypal -->
            			<xsl:otherwise>
							<PAYMENTMETHOD>Onlineueberweisung</PAYMENTMETHOD>	
							<PAYMENT_METHOD_ID>24</PAYMENT_METHOD_ID>	
              			</xsl:otherwise>
          			</xsl:choose>	
					<PAYMENT_CLASS>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/PAYMENT/PAYMENT_TERM" />
					</PAYMENT_CLASS>
					<PAYMENT_STATUS></PAYMENT_STATUS>
					<PAYMENT_COSTS></PAYMENT_COSTS>
					<PAYMENT_COSTS_VAT></PAYMENT_COSTS_VAT>
					<PAYMENT_CC_COMPANY></PAYMENT_CC_COMPANY>
					<PAYMENT_CC_NAME></PAYMENT_CC_NAME>
					<PAYMENT_CC_NO></PAYMENT_CC_NO>
					<PAYMENT_CC_CIC></PAYMENT_CC_CIC>
					<PAYMENT_CC_VALID></PAYMENT_CC_VALID>
					<PAYMENT_CC_AUTH></PAYMENT_CC_AUTH>
					<PAYMENT_BANK_NAME></PAYMENT_BANK_NAME>
					<PAYMENT_BANK_NO></PAYMENT_BANK_NO>
					<PAYMENT_BANK_BLZ></PAYMENT_BANK_BLZ>
					<PAYMENT_BANK_COMPANY></PAYMENT_BANK_COMPANY>
					<PAYMENT_BANK_BIC></PAYMENT_BANK_BIC>
					<PAYMENT_BANK_IBAN></PAYMENT_BANK_IBAN>
					<PAYMENT_BANK_AUTH></PAYMENT_BANK_AUTH>
					<!-- Versandarten -->
					<!--
					1 ups
					2 abholung
					3 kurier
					4 upsnachnahme
					5 upsexpress
					6 upssaver
					7 spedition
					8 dhl
					9 dhlexpress -->
					<xsl:choose>
							<!-- DE Versand -->
              			<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/COUNTRY = 'DE'">
              				<SHIPPING_METHOD>11</SHIPPING_METHOD>
							<SHIPPING_KONDITION>AbWerk</SHIPPING_KONDITION>
						</xsl:when>
						<!-- Schweiz Versand -->
						<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/COUNTRY = 'CH'">
              				<SHIPPING_METHOD>26</SHIPPING_METHOD>
							<SHIPPING_KONDITION>AbWerk</SHIPPING_KONDITION>
						</xsl:when>
						<!-- Norwegen Versand -->
						<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/COUNTRY = 'NO'">
              				<SHIPPING_METHOD>26</SHIPPING_METHOD>
							<SHIPPING_KONDITION>AbWerk</SHIPPING_KONDITION>
						</xsl:when>
						<!-- inland -->
						<xsl:when test="ORDER_HEADER/ORDER_INFO/DELIVERY_METHOD = 'freeshipping_freeshipping'">
              				<SHIPPING_METHOD>14</SHIPPING_METHOD>
							<SHIPPING_KONDITION>AbWerk</SHIPPING_KONDITION>
						</xsl:when>

            			<!-- Standart Post (multipletablerates_bestway_53) -->
            			<xsl:otherwise>
							<SHIPPING_METHOD>14</SHIPPING_METHOD>	
							<SHIPPING_KONDITION>AbWerk</SHIPPING_KONDITION>
              			</xsl:otherwise>
          			</xsl:choose>	
					<SHIPPING_CLASS>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/DELIVERY_METHOD" />
					</SHIPPING_CLASS>
					<!-- Unterscheidung zwischen Brutto und netto Kunden -->
					<xsl:choose>
              			<xsl:when test="ORDER_HEADER/ORDER_INFO/CUSTOMER_NET = '1'">
              						<SHIPPING_TOTAL_NET>
										<xsl:value-of select="translate(ORDER_HEADER/ORDER_INFO/DELIVERY_FEE, '.', ',')"/>
									</SHIPPING_TOTAL_NET>
									<SHIPPING_TOTAL_TAX>
										<xsl:value-of select="translate(ORDER_HEADER/ORDER_INFO/DELIVERY_FEE_TAX, '.', ',')"/>
									</SHIPPING_TOTAL_TAX>
									<SHIPPING_TOTAL_GROS>
										<xsl:value-of select="translate(ORDER_HEADER/ORDER_INFO/DELIVERY_FEE, '.', ',')"/>						
									</SHIPPING_TOTAL_GROS>
									<SHIPPING_TOTAL_VAT>0</SHIPPING_TOTAL_VAT>
									<SHIPPING_VAT>0</SHIPPING_VAT>
						</xsl:when>
						<!-- Brutto Kunde -->
            			<xsl:otherwise>
									<SHIPPING_TOTAL_NET>
										<xsl:value-of select="translate(ORDER_HEADER/ORDER_INFO/DELIVERY_FEE, '.', ',')"/>						
									</SHIPPING_TOTAL_NET>
									<SHIPPING_TOTAL_TAX>
											<xsl:value-of select="translate(ORDER_HEADER/ORDER_INFO/DELIVERY_FEE_TAX, '.', ',')"/>						
									</SHIPPING_TOTAL_TAX>
									<SHIPPING_TOTAL_GROS>
											<xsl:value-of select="translate(ORDER_HEADER/ORDER_INFO/DELIVERY_FEE_GROS, '.', ',')"/>															
									</SHIPPING_TOTAL_GROS>
									<SHIPPING_TOTAL_VAT></SHIPPING_TOTAL_VAT>
									<SHIPPING_VAT></SHIPPING_VAT>
              			</xsl:otherwise>
          			</xsl:choose>
					<SHIPPING_WEIGHT>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/DELIVERY_WEIGHT" />
					</SHIPPING_WEIGHT>
					<!-- zuschläge -->
					<CHARGES_METHOD></CHARGES_METHOD>
					<CHARGES_CLASS></CHARGES_CLASS>
					<CHARGES_TOTAL_NET></CHARGES_TOTAL_NET>
					<CHARGES_TOTAL_GROS></CHARGES_TOTAL_GROS>
					<CHARGES_TOTAL_VAT></CHARGES_TOTAL_VAT>
					<CHARGES_VAT></CHARGES_VAT>
					<!-- Abzüge -->
					<REBATES_METHOD></REBATES_METHOD>
					<REBATES_CLASS></REBATES_CLASS>
					<REBATES_TOTAL_NET></REBATES_TOTAL_NET>
					<REBATES_TOTAL_GROS></REBATES_TOTAL_GROS>
					<REBATES_TOTAL_VAT></REBATES_TOTAL_VAT>
					<REBATES_VAT></REBATES_VAT>
					<!-- Netto Kunde Auslandskunde-->
					<xsl:choose>
						<!-- EU Kunde -->
						<xsl:when test="ORDER_HEADER/ORDER_INFO/CUSTOMER_FOREIGN = '1'">
							<xsl:choose>
		              			<xsl:when test="ORDER_HEADER/ORDER_INFO/CUSTOMER_EU = '1'">
		              					<CUSTOMER_FOREIGN_EU_COUNTRY>
											<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/COUNTRY" />
										</CUSTOMER_FOREIGN_EU_COUNTRY>
										<CUSTOMER_FOREIGN_EU_VAT_ID>
											<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/VAT_ID" />
										</CUSTOMER_FOREIGN_EU_VAT_ID>
										<xsl:choose>
											<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/VAT_ID = ''">
												<CUSTOMER_TAX_ID>1</CUSTOMER_TAX_ID>
												<CUSTOMER_TAX_PERCENT>19</CUSTOMER_TAX_PERCENT>
											</xsl:when>
											<xsl:otherwise>
												<CUSTOMER_TAX_ID>1</CUSTOMER_TAX_ID>
												<CUSTOMER_TAX_PERCENT>19</CUSTOMER_TAX_PERCENT>
											</xsl:otherwise>
										</xsl:choose>	
								</xsl:when>
								<!-- anderes Ausland -->
								<xsl:otherwise>
									<CUSTOMER_FOREIGN_EU_COUNTRY></CUSTOMER_FOREIGN_EU_COUNTRY>	
									<CUSTOMER_FOREIGN_EU_VAT_ID></CUSTOMER_FOREIGN_EU_VAT_ID>
									<CUSTOMER_TAX_ID>4</CUSTOMER_TAX_ID>
									<CUSTOMER_TAX_PERCENT>0</CUSTOMER_TAX_PERCENT>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:when>
						<!-- Deutscher Kunde -->
            			<xsl:otherwise>
								<CUSTOMER_FOREIGN_EU_COUNTRY></CUSTOMER_FOREIGN_EU_COUNTRY>	
								<CUSTOMER_FOREIGN_EU_VAT_ID>
									<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/VAT_ID" />
								</CUSTOMER_FOREIGN_EU_VAT_ID>
								<CUSTOMER_TAX_ID>1</CUSTOMER_TAX_ID>
								<CUSTOMER_TAX_PERCENT>19</CUSTOMER_TAX_PERCENT>
              			</xsl:otherwise>
          			</xsl:choose>
					<CUSTOMER_FOREIGN>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/CUSTOMER_FOREIGN"/>
					</CUSTOMER_FOREIGN>
					<CUSTOMER_EU>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/CUSTOMER_EU"/>
					</CUSTOMER_EU>
					<CUSTOMER_NET>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/CUSTOMER_NET"/>
					</CUSTOMER_NET>                    
					<xsl:choose>
              			<xsl:when test="ORDER_HEADER/ORDER_INFO/CUSTOMER_NET = '1'">
              					<SELECTLINE_PREISTYP>N</SELECTLINE_PREISTYP>
						</xsl:when>
							<!-- Brutto Kunde -->
            			<xsl:otherwise>
								<SELECTLINE_PREISTYP>B</SELECTLINE_PREISTYP>	
              			</xsl:otherwise>
          			</xsl:choose>
					<!-- Adressen -->
					<MATCHCODE>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME" /><xsl:text> </xsl:text>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME2" /><xsl:text>, </xsl:text>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/CITY" />
					</MATCHCODE>
					<BILLINGADDRESS_PYUA>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/PREFIX" /><xsl:text> </xsl:text>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME" /><xsl:text> </xsl:text>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME2" />						
					</BILLINGADDRESS_PYUA>
					<BILLINGADDRESS_TITLE>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/PREFIX" />
					</BILLINGADDRESS_TITLE>
					<BILLINGADDRESS_NAME>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME" />
					</BILLINGADDRESS_NAME>	
					<BILLINGADDRESS_NAME2>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME2" />
					</BILLINGADDRESS_NAME2>	
					<BILLINGADDRESS_NAME3>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME3" />
					</BILLINGADDRESS_NAME3>	
					<BILLINGADDRESS_NAME4>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME" /><xsl:text> </xsl:text><xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME2" />
					</BILLINGADDRESS_NAME4>	
					
					<BILLINGADDRESS_ZIP>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/ZIP" />
					</BILLINGADDRESS_ZIP>	
					<BILLINGADDRESS_CITY>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/CITY" />
					</BILLINGADDRESS_CITY>	
					<BILLINGADDRESS_STREET>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/STREET" />
					</BILLINGADDRESS_STREET>	
					<BILLINGADDRESS_COUNTRY>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/COUNTRY" />
					</BILLINGADDRESS_COUNTRY>	
					<xsl:choose>
              			<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/COUNTRY = 'DE'">
              				<BILLINGADDRESS_COUNTRY_CODE>DE</BILLINGADDRESS_COUNTRY_CODE>
							<BILLINGADDRESS_NAME5>
								<xsl:text>z. Hd. </xsl:text><xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME" /><xsl:text> </xsl:text><xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME2" />
							</BILLINGADDRESS_NAME5>		
						</xsl:when>
						<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/COUNTRY = 'AT'">
              				<BILLINGADDRESS_COUNTRY_CODE>AT</BILLINGADDRESS_COUNTRY_CODE>
							<BILLINGADDRESS_NAME5>
								<xsl:text>z. Hd. </xsl:text><xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME" /><xsl:text> </xsl:text><xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME2" />
							</BILLINGADDRESS_NAME5>		
						</xsl:when>
            			<!-- Stand ISO Code-->
            			<xsl:otherwise>
							<BILLINGADDRESS_COUNTRY_CODE>DE</BILLINGADDRESS_COUNTRY_CODE>	
							<BILLINGADDRESS_NAME5>
								<xsl:text>to </xsl:text><xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME" /><xsl:text> </xsl:text><xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME2" />
							</BILLINGADDRESS_NAME5>	
              			</xsl:otherwise>
          			</xsl:choose>
					<BILLINGADDRESS_VAT_ID>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/VAT_ID" />
					</BILLINGADDRESS_VAT_ID>
					<BILLINGADDRESS_PHONE>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/PHONE" />
					</BILLINGADDRESS_PHONE>
					<BILLINGADDRESS_PHONE2>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/PHONE2" />
					</BILLINGADDRESS_PHONE2>
					<BILLINGADDRESS_FAX>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/FAX" />
					</BILLINGADDRESS_FAX>
					<BILLINGADDRESS_EMAIL>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/EMAIL" />
					</BILLINGADDRESS_EMAIL>
					<CUSTOMERADRESS_TITLE>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/PREFIX" />
					</CUSTOMERADRESS_TITLE>
					<CUSTOMERADRESS_NAME>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME" />
					</CUSTOMERADRESS_NAME>	
					<CUSTOMERADRESS_NAME2>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME2" />
					</CUSTOMERADRESS_NAME2>	
					<CUSTOMERADRESS_NAME3>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/NAME3" />
					</CUSTOMERADRESS_NAME3>	
					<CUSTOMERADRESS_ZIP>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/ZIP" />
					</CUSTOMERADRESS_ZIP>	
					<CUSTOMERADRESS_CITY>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/CITY" />
					</CUSTOMERADRESS_CITY>	
					<CUSTOMERADRESS_STREET>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/STREET" />
					</CUSTOMERADRESS_STREET>	
					<CUSTOMERADRESS_COUNTRY>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/COUNTRY" />
					</CUSTOMERADRESS_COUNTRY>	
					<xsl:choose>
              			<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/COUNTRY = 'DE'">
              				<CUSTOMERADRESS_COUNTRY_CODE>DE</CUSTOMERADRESS_COUNTRY_CODE>
						</xsl:when>
						<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/COUNTRY = 'AT'">
              				<CUSTOMERADRESS_COUNTRY_CODE>AT</CUSTOMERADRESS_COUNTRY_CODE>
						</xsl:when>
            			<!-- Stand ISO Code-->
            			<xsl:otherwise>
							<CUSTOMERADRESS_COUNTRY_CODE>DE</CUSTOMERADRESS_COUNTRY_CODE>	
              			</xsl:otherwise>
          			</xsl:choose>					
					<CUSTOMERADRESS_VAT_ID>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/VAT_ID" />
					</CUSTOMERADRESS_VAT_ID>
					<CUSTOMERADRESS_PHONE>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/PHONE" />
					</CUSTOMERADRESS_PHONE>
					<CUSTOMERADRESS_PHONE2>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/PHONE2" />
					</CUSTOMERADRESS_PHONE2>
					<CUSTOMERADRESS_FAX>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/FAX" />
					</CUSTOMERADRESS_FAX>
					<CUSTOMERADRESS_EMAIL>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/INVOICE_PARTY/PARTY/ADDRESS/EMAIL" />
					</CUSTOMERADRESS_EMAIL>
					<SHIPPINGADDRESS_PYUA>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/PREFIX" /><xsl:text> </xsl:text>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/NAME" /><xsl:text> </xsl:text>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/NAME2" />						
					</SHIPPINGADDRESS_PYUA>
					<SHIPPINGADDRESS_TITLE>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/PREFIX" />
					</SHIPPINGADDRESS_TITLE>
					<SHIPPINGADDRESS_NAME>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/NAME" />
					</SHIPPINGADDRESS_NAME>	
					<SHIPPINGADDRESS_NAME2>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/NAME2" />
					</SHIPPINGADDRESS_NAME2>	
					<SHIPPINGADDRESS_NAME3>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/NAME3" />
					</SHIPPINGADDRESS_NAME3>
					<SHIPPINGADDRESS_NAME4>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/NAME" /><xsl:text> </xsl:text>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/NAME2" />
					</SHIPPINGADDRESS_NAME4>					
					<SHIPPINGADDRESS_ZIP>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/ZIP" />
					</SHIPPINGADDRESS_ZIP>	
					<SHIPPINGADDRESS_CITY>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/CITY" />
					</SHIPPINGADDRESS_CITY>	
					<SHIPPINGADDRESS_STREET>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/STREET" />
					</SHIPPINGADDRESS_STREET>	
					<SHIPPINGADDRESS_COUNTRY>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/COUNTRY" />
					</SHIPPINGADDRESS_COUNTRY>	
					<xsl:choose>
              			<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/COUNTRY = 'DE'">
              				<SHIPPINGADDRESS_COUNTRY_CODE>DE</SHIPPINGADDRESS_COUNTRY_CODE>
						</xsl:when>
						<xsl:when test="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/COUNTRY = 'AT'">
              				<SHIPPINGADDRESS_COUNTRY_CODE>AT</SHIPPINGADDRESS_COUNTRY_CODE>
						</xsl:when>
            			<!-- Stand ISO Code-->
            			<xsl:otherwise>
							<SHIPPINGADDRESS_COUNTRY_CODE>DE</SHIPPINGADDRESS_COUNTRY_CODE>	
              			</xsl:otherwise>
          			</xsl:choose>		
					<SHIPPINGADDRESS_VAT_ID>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/VAT_ID" />
					</SHIPPINGADDRESS_VAT_ID>
					<SHIPPINGADDRESS_PHONE>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/PHONE" />
					</SHIPPINGADDRESS_PHONE>
					<SHIPPINGADDRESS_PHONE2>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/PHONE2" />
					</SHIPPINGADDRESS_PHONE2>
					<SHIPPINGADDRESS_FAX>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/FAX" />
					</SHIPPINGADDRESS_FAX>
					<SHIPPINGADDRESS_EMAIL>
						<xsl:value-of select="ORDER_HEADER/ORDER_INFO/ORDER_PARTIES/BUYER_PARTY/PARTY/ADDRESS/EMAIL" />
					</SHIPPINGADDRESS_EMAIL>
					<!-- Ende Adressen -->
				</ORDER_HEADER>
					<!-- Bestellpositionen -->
					<ORDER_PRODUCTS>
					<xsl:for-each select="ORDER_ITEM_LIST/ORDER_ITEM">
						<PRODUCT>
							<PRODUCTS_POS>
								<xsl:value-of select="LINE_ITEM_ID"/>
							</PRODUCTS_POS>
							<PRODUCTS_ID></PRODUCTS_ID>
							<ORDER_ID>
								<xsl:value-of select="ARTICLE_ID/PRODUCTS_ORDER_ID"/>
							</ORDER_ID>					
							<ORDERS_YYYYMMDD>
								<xsl:value-of select="substring(../../ORDER_HEADER/ORDER_INFO/ORDER_DATE, 1, 4)" />
								<xsl:value-of select="substring(../../ORDER_HEADER/ORDER_INFO/ORDER_DATE, 6, 2)" />
								<xsl:value-of select="substring(../../ORDER_HEADER/ORDER_INFO/ORDER_DATE, 9, 2)" />  
							</ORDERS_YYYYMMDD>
							<PRODUCTS_QUANTITY>
								<xsl:value-of select='format-number(QUANTITY, "#")'/>
							</PRODUCTS_QUANTITY>
							<!-- Bei Versandkosten Artikelnummer zuordnen -->
							<xsl:choose>
							    <xsl:when test="ARTICLE_ID/SUPPLIER_AID = 'multipletablerates_bestway_53'">
              						    <PRODUCTS_MODEL>55012110</PRODUCTS_MODEL>
            				    </xsl:when>
							<!-- Versand DE -->
						        <xsl:when test="ARTICLE_ID/SUPPLIER_AID = 'multipletablerates_bestway_79'">
              						    <PRODUCTS_MODEL>55012110</PRODUCTS_MODEL>
            				    </xsl:when>
								<!-- Auslands Versand -->
              					<xsl:when test="contains(ARTICLE_ID/SUPPLIER_AID, 'multipletablerates_bestway')">
											<PRODUCTS_MODEL>55012110</PRODUCTS_MODEL>
            				     </xsl:when>
								<!-- Pauschale Versandkosten -->
								<xsl:when test="contains(ARTICLE_ID/SUPPLIER_AID, 'tablerate_bestway')">
											<PRODUCTS_MODEL>55012110</PRODUCTS_MODEL>
            				     </xsl:when>
								<xsl:when test="contains(ARTICLE_ID/SUPPLIER_AID, 'flatrate_flat')">
											<PRODUCTS_MODEL>55012110</PRODUCTS_MODEL>
            				    </xsl:when>
								<xsl:when test="contains(ARTICLE_ID/SUPPLIER_AID, 'freeshipping_freeshipping')">
											<PRODUCTS_MODEL>55012110</PRODUCTS_MODEL>
            				    </xsl:when>
								
						       <!-- Standard Artikel - NICHT Versandkosten-->
            					       <xsl:otherwise>
                				       <PRODUCTS_MODEL>
											<xsl:value-of select="ARTICLE_ID/SUPPLIER_AID"/>
										</PRODUCTS_MODEL>
              					</xsl:otherwise>
          					</xsl:choose>
							<PRODUCTS_ATTRIBUTE>
										<xsl:value-of select="ARTICLE_ID/ATTRIBUTE_ID"/>
							</PRODUCTS_ATTRIBUTE>
							<PRODUCTS_ATTRIBUTE>
										<xsl:value-of select="ARTICLE_ID/ATTRIBUTE_ID"/>
							</PRODUCTS_ATTRIBUTE>
							<PRODUCTS_ATTRIBUTE>
										<xsl:value-of select="ARTICLE_ID/ATTRIBUTE_ID"/>
							</PRODUCTS_ATTRIBUTE>
							<COLOR_ID>
										<xsl:value-of select="ARTICLE_ID/ATTRIBUTE_ID"/>
							</COLOR_ID>
							<SIZE>
										<xsl:value-of select="ARTICLE_ID/ATTRIBUTE_ID_3"/>
							</SIZE>							
							<PRODUCTS_EAN></PRODUCTS_EAN>
							<PRODUCTS_NAME>
								<xsl:value-of select="ARTICLE_ID/DESCRIPTION_SHORT"/>
							</PRODUCTS_NAME>
							<!-- EUROPA3000 M=Manueller Artikel, N=Standardartikel, B=weiterer Text zu Artikel, T=Text mit Inhalt in _F40, D=Text mit Inhalt in _F40 -->
							<PRODUCTS_TYPE>
										<xsl:value-of select="ARTICLE_ID/ARTICLE_TYPE"/>
							</PRODUCTS_TYPE>
							<!-- EUROPA3000 M=Manueller Artikel, N=Standardartikel, B=weiterer Text zu Artikel, T=Text mit Inhalt in _F40, D=Text mit Inhalt in _F40 -->
							<xsl:choose>
              					<xsl:when test="ARTICLE_ID/ARTICLE_TYPE = 'B'">
              						<PRODUCTS_PRICE_NET>
									</PRODUCTS_PRICE_NET>
									<PRODUCTS_TOTAL_NET>
									</PRODUCTS_TOTAL_NET>
									<PRODUCTS_PRICE_GROS>
									</PRODUCTS_PRICE_GROS>
									<PRODUCTS_TOTAL_GROS>
									</PRODUCTS_TOTAL_GROS>
									<PRODUCTS_TAX>
									</PRODUCTS_TAX>
									<PRODUCTS_TAX_AMOUNT>
							   		</PRODUCTS_TAX_AMOUNT>
									<PRODUCTS_TAX_FLAG>
									</PRODUCTS_TAX_FLAG>
									<PRODUCTS_DISCOUNT_AMOUNT>0
									</PRODUCTS_DISCOUNT_AMOUNT>
									<PRODUCTS_DISCOUNT_PERCENT>0</PRODUCTS_DISCOUNT_PERCENT>
									<PRODUCTS_UNIT></PRODUCTS_UNIT>
            				    </xsl:when>
            				    <!-- Standardartikel -->
            					<xsl:otherwise>
									<!-- Unterscheidung zwischen Brutto und netto Kunden -->
									<xsl:choose>
				              			<xsl:when test="ORDER_HEADER/ORDER_INFO/CUSTOMER_NET = '1'">
				              						<PRODUCTS_PRICE_NET>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/PRICE_AMOUNT, '.', ',')"/>
													</PRODUCTS_PRICE_NET>
													<PRODUCTS_TOTAL_NET>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/PRICE_LINE_AMOUNT, '.', ',')"/>
													</PRODUCTS_TOTAL_NET>
													<PRODUCTS_PRICE_GROS>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/PRICE_AMOUNT, '.', ',')"/>
													</PRODUCTS_PRICE_GROS>
													<PRODUCTS_TOTAL_GROS>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/PRICE_LINE_AMOUNT, '.', ',')"/>
													</PRODUCTS_TOTAL_GROS>
													<PRODUCTS_TAX>0</PRODUCTS_TAX>
													<PRODUCTS_TAX_AMOUNT>0</PRODUCTS_TAX_AMOUNT>
													<PRODUCTS_TAX_FLAG>
														<xsl:value-of select="PRICE_FLAG"/>
													</PRODUCTS_TAX_FLAG>
													<PRODUCTS_DISCOUNT_AMOUNT>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/DISCOUNT_AMOUNT, '.', ',')"/>
													</PRODUCTS_DISCOUNT_AMOUNT>
													<PRODUCTS_DISCOUNT_PERCENT>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/DISCOUNT_PERCENT, '.', ',')"/>
													</PRODUCTS_DISCOUNT_PERCENT>
													<PRODUCTS_DISCOUNT_PRICE_AMOUNT_NET>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/DISCOUNT_PRICE_AMOUNT, '.', ',')"/>
													</PRODUCTS_DISCOUNT_PRICE_AMOUNT_NET>
													<PRODUCTS_DISCOUNT_PRICE_LINE_AMOUNT_NET>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/DISCOUNT_PRICE_LINE_AMOUNT, '.', ',')"/>
													</PRODUCTS_DISCOUNT_PRICE_LINE_AMOUNT_NET>
													<PRODUCTS_DISCOUNT_PRICE_AMOUNT>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/DISCOUNT_PRICE_AMOUNT, '.', ',')"/>
													</PRODUCTS_DISCOUNT_PRICE_AMOUNT>
													<PRODUCTS_DISCOUNT_PRICE_LINE_AMOUNT>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/DISCOUNT_PRICE_LINE_AMOUNT, '.', ',')"/>
													</PRODUCTS_DISCOUNT_PRICE_LINE_AMOUNT>
													<PRODUCTS_UNIT></PRODUCTS_UNIT>
										</xsl:when>
										<!-- Brutto Kunde -->
				            			<xsl:otherwise>
													<PRODUCTS_PRICE_NET>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/PRICE_AMOUNT, '.', ',')"/>
													</PRODUCTS_PRICE_NET>
													<PRODUCTS_TOTAL_NET>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/PRICE_LINE_AMOUNT, '.', ',')"/>
													</PRODUCTS_TOTAL_NET>
													<PRODUCTS_PRICE_GROS>
														<xsl:value-of select="translate(ARTICLE_PRICE_GROS/PRICE_AMOUNT, '.', ',')"/>
													</PRODUCTS_PRICE_GROS>
													<PRODUCTS_TOTAL_GROS>
														<xsl:value-of select="translate(ARTICLE_PRICE_GROS/PRICE_LINE_AMOUNT, '.', ',')"/>
													</PRODUCTS_TOTAL_GROS>
													<PRODUCTS_TAX>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/TAX, '.', ',')"/>
													</PRODUCTS_TAX>
													<PRODUCTS_TAX_AMOUNT>
											   			<xsl:value-of select="translate(ARTICLE_PRICE_NET/TAX_AMOUNT, '.', ',')"/>
													</PRODUCTS_TAX_AMOUNT>
													<PRODUCTS_TAX_FLAG>
														<xsl:value-of select="PRICE_FLAG"/>
													</PRODUCTS_TAX_FLAG>
													<PRODUCTS_DISCOUNT_AMOUNT>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/DISCOUNT_AMOUNT, '.', ',')"/>
													</PRODUCTS_DISCOUNT_AMOUNT>
													<PRODUCTS_DISCOUNT_PERCENT>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/DISCOUNT_PERCENT, '.', ',')"/>
													</PRODUCTS_DISCOUNT_PERCENT>
													<PRODUCTS_DISCOUNT_PRICE_AMOUNT_NET>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/DISCOUNT_PRICE_AMOUNT, '.', ',')"/>
													</PRODUCTS_DISCOUNT_PRICE_AMOUNT_NET>
													<PRODUCTS_DISCOUNT_PRICE_LINE_AMOUNT_NET>
														<xsl:value-of select="translate(ARTICLE_PRICE_NET/DISCOUNT_PRICE_LINE_AMOUNT, '.', ',')"/>
													</PRODUCTS_DISCOUNT_PRICE_LINE_AMOUNT_NET>
													<PRODUCTS_DISCOUNT_PRICE_AMOUNT>
														<xsl:value-of select="translate(ARTICLE_PRICE_GROS/DISCOUNT_PRICE_AMOUNT, '.', ',')"/>
													</PRODUCTS_DISCOUNT_PRICE_AMOUNT>
													<PRODUCTS_DISCOUNT_PRICE_LINE_AMOUNT>
														<xsl:value-of select="translate(ARTICLE_PRICE_GROS/DISCOUNT_PRICE_LINE_AMOUNT, '.', ',')"/>
													</PRODUCTS_DISCOUNT_PRICE_LINE_AMOUNT>
													<PRODUCTS_UNIT></PRODUCTS_UNIT>
				              			</xsl:otherwise>
				          			</xsl:choose>
                				</xsl:otherwise>
          					</xsl:choose>
							<PRODUCTS_DESCRIPTION></PRODUCTS_DESCRIPTION>
							<PRODUCTS_SHORT_DESCRIPTION>
								<xsl:value-of select="ARTICLE_ID/DESCRIPTION_SHORT"/>
							</PRODUCTS_SHORT_DESCRIPTION>
							<PRODUCTS_OPTIONS>
								<xsl:value-of select="ARTICLE_ID/OPTIONS"/>
							</PRODUCTS_OPTIONS>
							<PRODUCTS_DELIVERY_DATE>
									<xsl:value-of select="substring(ORDER_LIST/ORDER/ORDER_HEADER/ORDER_INFO/ORDER_DATE, 9, 2)" /><xsl:text>.</xsl:text>
						            <xsl:value-of select="substring(ORDER_LIST/ORDER/ORDER_HEADER/ORDER_INFO/ORDER_DATE, 6, 2)" /><xsl:text>.</xsl:text>
						            <xsl:value-of select="substring(ORDER_LIST/ORDER/ORDER_HEADER/ORDER_INFO/ORDER_DATE, 1, 4)" />						
							</PRODUCTS_DELIVERY_DATE>					
						</PRODUCT>
					</xsl:for-each>				
					</ORDER_PRODUCTS>
				</ORDER_INFO>
			</xsl:for-each>
		</ORDER>
   </xsl:template>

</xsl:stylesheet>


