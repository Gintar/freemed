/*
 * $Id$
 *
 * Authors:
 *      Jeff Buchbinder <jeff@freemedsoftware.org>
 *
 * FreeMED Electronic Medical Record and Practice Management System
 * Copyright (C) 1999-2009 FreeMED Software Foundation
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */

package org.freemedsoftware.gwt.client.widget;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

import org.freemedsoftware.gwt.client.JsonUtil;
import org.freemedsoftware.gwt.client.PatientEntryScreenInterface;
import org.freemedsoftware.gwt.client.Util;
import org.freemedsoftware.gwt.client.WidgetInterface;
import org.freemedsoftware.gwt.client.Api.PatientInterfaceAsync;
import org.freemedsoftware.gwt.client.Util.ProgramMode;
import org.freemedsoftware.gwt.client.screen.PatientScreen;
import org.freemedsoftware.gwt.client.screen.patient.LetterEntry;
import org.freemedsoftware.gwt.client.screen.patient.PatientCorrespondenceEntry;
import org.freemedsoftware.gwt.client.screen.patient.PatientIdEntry;
import org.freemedsoftware.gwt.client.screen.patient.ProgressNoteEntry;
import org.freemedsoftware.gwt.client.screen.patient.ReferralEntry;
import org.freemedsoftware.gwt.client.widget.CustomTable.TableWidgetColumnSetInterface;

import com.google.gwt.core.client.GWT;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.http.client.URL;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.user.client.Window;
import com.google.gwt.user.client.rpc.AsyncCallback;
import com.google.gwt.user.client.ui.CheckBox;
import com.google.gwt.user.client.ui.Composite;
import com.google.gwt.user.client.ui.DialogBox;
import com.google.gwt.user.client.ui.HasHorizontalAlignment;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.Image;
import com.google.gwt.user.client.ui.Label;
import com.google.gwt.user.client.ui.PushButton;
import com.google.gwt.user.client.ui.SimplePanel;
import com.google.gwt.user.client.ui.TabPanel;
import com.google.gwt.user.client.ui.TextArea;
import com.google.gwt.user.client.ui.VerticalPanel;
import com.google.gwt.user.client.ui.Widget;

public class PatientProblemList extends WidgetInterface {

	public class ActionBar extends Composite implements ClickHandler {

		protected final String IMAGE_ANNOTATE = "resources/images/add1.16x16.png";

		protected final String IMAGE_DELETE = "resources/images/summary_delete.16x16.png";

		protected final String IMAGE_MODIFY = "resources/images/summary_modify.16x16.png";

		protected final String IMAGE_PRINT = "resources/images/ico.printer.16x16.png";

		protected Integer internalId = 0;

		protected HashMap<String, String> data = null;

		protected Image annotateImage = null, deleteImage = null,
				modifyImage = null, unlockedImage = null, lockedImage = null,
				printImage = null;

		protected CheckBox cb = null;

		public ActionBar(HashMap<String, String> item) {
			// Pull ID for future
			internalId = Integer.parseInt(item.get("id"));
			data = item;

			boolean locked = (Integer.parseInt(data.get("locked")) > 0);

			HorizontalPanel hPanel = new HorizontalPanel();
			initWidget(hPanel);

			// Multiple select box
			cb = new CheckBox();
			cb.addClickHandler(this);
			hPanel.add(cb);

			// Build icons
			annotateImage = new Image(IMAGE_ANNOTATE);
			annotateImage.setTitle("Add Annotation");
			annotateImage.addClickHandler(this);
			hPanel.add(annotateImage);

			printImage = new Image(IMAGE_PRINT);
			printImage.setTitle("Print");
			printImage.addClickHandler(this);
			hPanel.add(printImage);

			// Display all unlocked things
			if (!locked) {
				deleteImage = new Image(IMAGE_DELETE);
				deleteImage.setTitle("Remove");
				deleteImage.addClickHandler(this);
				hPanel.add(deleteImage);

				modifyImage = new Image(IMAGE_MODIFY);
				modifyImage.setTitle("Edit");
				modifyImage.addClickHandler(this);
				hPanel.add(modifyImage);
			} else {
				// Display all actions for locked items
			}
		}

		public void setChecked(boolean s) {
			cb.setValue(s);
		}

		public void onClick(ClickEvent evt) {
			Widget sender = (Widget) evt.getSource();
			if (sender == cb) {
				// Handle clicking
				JsonUtil.debug("current status = "
						+ (cb.getValue() ? "checked" : "not"));

				// Set in dictionary
				setSelected(internalId, cb.getValue());

				// Adjust all others to have same status as this.
				try {
					Iterator<ActionBar> iter = Arrays.asList(
							actionBarMap.get(internalId)).iterator();
					while (iter.hasNext()) {
						ActionBar cur = iter.next();
						if (cur != this) {
							cur.setChecked(cb.getValue());
						}
					}
				} catch (Exception ex) {
					JsonUtil.debug(ex.toString());
				}
			} else if (sender == annotateImage) {
				CreateAnnotationPopup p = new CreateAnnotationPopup(data);
				p.center();
			} else if (sender == printImage) {
				EmrPrintDialog d = new EmrPrintDialog();
				d.setItems(new Integer[] { Integer.parseInt(data.get("id")) });
				d.center();
			} else if (sender == deleteImage) {
				if (Window
						.confirm("Are you sure you want to delete this item?")) {
					deleteItem(internalId);
				}
			} else if (sender == modifyImage) {
				modifyItem(internalId, data);
			} else {
				// Do nothing
			}
		}
	}

	public class CreateAnnotationPopup extends DialogBox implements
			ClickHandler {

		protected HashMap<String, String> data = null;

		protected TextArea textArea = null;

		public CreateAnnotationPopup(HashMap<String, String> rec) {
			super(true);
			setAnimationEnabled(true);

			// Save copy of data
			data = rec;

			final VerticalPanel verticalPanel = new VerticalPanel();
			setStylePrimaryName("freemed-CreateAnnotationPopup");
			setWidget(verticalPanel);

			textArea = new TextArea();
			textArea.setSize("300px", "300px");
			verticalPanel.add(textArea);

			PushButton submitButton = new PushButton("Add Annotation");
			submitButton.addClickHandler(this);
			submitButton.setText("Add Annotation");
			verticalPanel.add(submitButton);
			verticalPanel.setCellHorizontalAlignment(submitButton,
					HasHorizontalAlignment.ALIGN_CENTER);
		}

		public void onClick(ClickEvent evt) {
			Window.alert("STUB: need to handle annotation add");
			hide();
		}

	}

	protected Integer patientId = new Integer(0);

	protected TabPanel tabPanel = null;

	protected HashMap<String, CustomTable> tables = new HashMap<String, CustomTable>();

	protected HashMap<String, Label> messages = new HashMap<String, Label>();

	protected HashMap<String, String>[] dataStore = null;

	protected HashMap<Integer, ActionBar[]> actionBarMap = new HashMap<Integer, ActionBar[]>();

	protected List<Integer> selected = new ArrayList<Integer>();

	protected int maximumRows = 10;

	protected PatientScreen patientScreen = null;

	public PatientProblemList() {
		SimplePanel panel = new SimplePanel();
		tabPanel = new TabPanel();
		tabPanel.setSize("100%", "100%");
		tabPanel.setVisible(true);
		panel.setWidget(tabPanel);
		initWidget(panel);

		// All
		Image allImage = new Image("resources/images/chart_full.16x16.png");
		allImage.setTitle("All");
		createSummaryTable(allImage, "all");
		// Progress Notes
		Image notesImage = new Image("resources/images/chart.16x16.png");
		notesImage.setTitle("Progress Notes");
		createSummaryTable(notesImage, "pnotes");
		// Letters
		Image lettersImage = new Image(
				"resources/images/summary_envelope.16x16.png");
		lettersImage.setTitle("Letters");
		createSummaryTable(lettersImage, "letters,patletter");

		tabPanel.selectTab(0);
	}

	public void modifyItem(Integer item, HashMap<String, String> data) {
		PatientEntryScreenInterface i = resolvePatientScreen(data.get("module"));
		i.setInternalId(Integer.parseInt(data.get("oid")));
		Util.spawnTabPatient("Modify", i, patientScreen);
	}

	public void deleteItem(Integer item) {

	}

	public void setPatientId(Integer id) {
		patientId = id;
		// Call initial data load, as patient id is set
		loadData();
	}

	public void setPatientScreen(PatientScreen ps) {
		patientScreen = ps;
	}

	public void addToActionBarMap(Integer key, ActionBar ab) {
		ActionBar[] x = actionBarMap.get(key);
		if (x == null) {
			// Create new
			actionBarMap.put(key, new ActionBar[] { ab });
		} else {
			// Add to map
			List<ActionBar> l = new ArrayList<ActionBar>();
			for (int iter = 0; iter < x.length; iter++) {
				l.add(x[iter]);
			}
			l.add(ab);
			actionBarMap.put(key, (ActionBar[]) l.toArray(new ActionBar[0]));
		}
	}

	public void setMaximumRows(int maxRows) {
		maximumRows = maxRows;
		Iterator<String> iter = tables.keySet().iterator();
		while (iter.hasNext()) {
			String k = iter.next();
			tables.get(k).setMaximumRows(maximumRows);
		}
	}

	private void createSummaryTable(Widget tab, String criteria) {
		CustomTable t = new CustomTable();
		t.setTableWidgetColumnSetInterface(new TableWidgetColumnSetInterface() {
			public Widget setColumn(String columnName,
					HashMap<String, String> data) {
				// Render only action column, otherwise skip renderer
				if (columnName.compareToIgnoreCase("action") != 0) {
					return null;
				}
				ActionBar ab = new ActionBar(data);
				// Add to mapping, so we can control lots of these things
				addToActionBarMap(Integer.parseInt(data.get("id")), ab);
				// Push value back to table
				return ab;
			}
		});
		t.setAllowSelection(false);
		t.setMaximumRows(maximumRows);
		t.addColumn("Date", "date_mdy");
		t.addColumn("Module", "type");
		t.addColumn("Summary", "summary");
		t.addColumn("Action", "action");

		VerticalPanel vPanel = new VerticalPanel();
		vPanel.add(t);

		Label m = new Label();
		m.setText("No items.");
		m.setStylePrimaryName("freemed-MessageText");
		m.setVisible(false);
		vPanel.add(m);

		tabPanel.add(vPanel, tab);

		tables.put(criteria, t);
		messages.put(criteria, m);
	}

	protected PatientEntryScreenInterface resolvePatientScreen(String moduleName) {
		PatientEntryScreenInterface pSI = null;
		if (moduleName.toLowerCase() == "pnotes") {
			pSI = new ProgressNoteEntry();
		}
		if (moduleName.toLowerCase() == "letters") {
			pSI = new LetterEntry();
		}
		if (moduleName.toLowerCase() == "patletter") {
			pSI = new PatientCorrespondenceEntry();
		}
		if (moduleName.toLowerCase() == "patient_ids") {
			pSI = new PatientIdEntry();
		}
		if (moduleName.toLowerCase() == "referrals") {
			pSI = new ReferralEntry();
		}
		if (pSI != null) {
			pSI.setPatientId(patientId);
			pSI.assignPatientScreen(patientScreen);
		}
		return pSI;
	}

	@SuppressWarnings("unchecked")
	protected void loadData() {
		// Clear mappings during populate
		selected.clear();
		actionBarMap.clear();

		if (patientId.intValue() == 0) {
			JsonUtil
					.debug("ERROR: patientId not defined when loadData called for PatientProblemList");
		}
		if (Util.getProgramMode() == ProgramMode.STUBBED) {
			List<HashMap<String, String>> a = new ArrayList<HashMap<String, String>>();
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("stamp", "2008-01-01");
				item.put("type", "test");
				item.put("summary", "Test item 1");
				item.put("module", "ProgressNotes");
				a.add(item);
			}
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("stamp", "2008-01-02");
				item.put("type", "test");
				item.put("summary", "Test item 2");
				item.put("module", "ProgressNotes");
				a.add(item);
			}
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("stamp", "2008-01-02");
				item.put("type", "test");
				item.put("summary", "Test item 3");
				item.put("module", "Letters");
				a.add(item);
			}
			{
				HashMap<String, String> item = new HashMap<String, String>();
				item.put("stamp", "2008-01-03");
				item.put("type", "test");
				item.put("summary", "Test item 4");
				item.put("module", "Letters");
				a.add(item);
			}
			dataStore = (HashMap<String, String>[]) a
					.toArray(new HashMap<?, ?>[0]);
			populateData(dataStore);
		} else if (Util.getProgramMode() == ProgramMode.JSONRPC) {
			String[] params = { patientId.toString() };
			RequestBuilder builder = new RequestBuilder(
					RequestBuilder.POST,
					URL
							.encode(Util
									.getJsonRequest(
											"org.freemedsoftware.api.PatientInterface.EmrAttachmentsByPatient",
											params)));
			try {
				builder.sendRequest(null, new RequestCallback() {
					public void onError(Request request, Throwable ex) {
						Window.alert(ex.toString());
					}

					public void onResponseReceived(Request request,
							Response response) {
						JsonUtil.debug("onResponseReceived");
						if (Util.checkValidSessionResponse(response.getText())) {
							if (200 == response.getStatusCode()) {
								JsonUtil.debug(response.getText());
								HashMap<String, String>[] r = (HashMap<String, String>[]) JsonUtil
										.shoehornJson(JSONParser.parse(response
												.getText()),
												"HashMap<String,String>[]");
								if (r != null) {
									JsonUtil
											.debug("PatientProblemList... r.length = "
													+ new Integer(r.length)
															.toString());
									dataStore = r;
									populateData(dataStore);
								}
							} else {
								Window.alert(response.toString());
							}
						}
					}
				});
			} catch (RequestException e) {
				Window.alert(e.toString());
			}
		} else {
			PatientInterfaceAsync service = null;
			try {
				service = (PatientInterfaceAsync) Util
						.getProxy("org.freemedsoftware.gwt.client.Api.PatientInterface");
			} catch (Exception e) {
				GWT.log("Failed to get proxy for PatientInterface", e);
			}
			service.EmrAttachmentsByPatient(patientId,
					new AsyncCallback<HashMap<String, String>[]>() {
						public void onSuccess(HashMap<String, String>[] r) {
							dataStore = r;
							populateData(dataStore);
						}

						public void onFailure(Throwable t) {
							GWT.log("Exception", t);
						}
					});
		}
	}

	/**
	 * Check to see if a string is in a stack of pipe separated values.
	 * 
	 * @param needle
	 * @param haystack
	 * @return
	 */
	protected boolean inSet(String needle, String haystack) {
		// Handle incidence of needle == haystack
		if (needle.equalsIgnoreCase(haystack)) {
			return true;
		}
		String[] stack = haystack.split(",");
		for (int iter = 0; iter < stack.length; iter++) {
			if (needle.trim().equalsIgnoreCase(stack[iter].trim())) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Set an item as being selected.
	 * 
	 * @param key
	 * @param isSet
	 */
	protected void setSelected(Integer key, boolean isSet) {
		try {
			if (isSet) {
				selected.add(key);
			} else {
				selected.remove(key);
			}
		} catch (Exception ex) {
		}
	}

	/**
	 * Internal method to populate all sub tables.
	 * 
	 * @param data
	 */
	@SuppressWarnings("unchecked")
	protected void populateData(HashMap<String, String>[] data) {
		JsonUtil.debug("PatientProblemList.populateData");
		Iterator<String> tIter = tables.keySet().iterator();
		while (tIter.hasNext()) {
			String k = tIter.next();
			JsonUtil.debug("Populating table " + k);

			// Clear table contents
			try {
				tables.get(k).clearData();
			} catch (Exception ex) {
			}

			// Depending on criteria, etc, choose what do to.
			String crit = k;
			// JsonUtil.debug(" --> got criteria = " + crit);
			// boolean star = tables.get(k).getStarred();

			List<HashMap<String, String>> res = new ArrayList<HashMap<String, String>>();
			List<HashMap<String, String>> d = Arrays.asList(data);
			Iterator<HashMap<String, String>> iter = d.iterator();
			while (iter.hasNext()) {
				HashMap<String, String> rec = iter.next();

				// TODO: handle star

				if (crit == null || crit.length() == 0
						|| crit.contentEquals("all")) {
					// Don't handle criteria at all, effectively passthru
					// JsonUtil.debug("-- pass through, no criteria");
				} else {
					// Handle criteria
					if (!inSet(rec.get("module"), crit)) {
						JsonUtil.debug(rec.get("module") + " not include "
								+ crit);
					} else {
						JsonUtil.debug(rec.get("module") + " INCLUDE " + crit);
					}
					if (!inSet(rec.get("module"), crit)) {
						continue;
					}
				}

				// If it passes all criteria, add to the stack for the result.
				res.add(rec);
			}

			if (res.size() > 0) {
				messages.get(crit).setVisible(false);
				JsonUtil.debug("Populating table " + k + " with "
						+ new Integer(res.size()).toString() + " entries");
				CustomTable thisTable = tables.get(k);
				HashMap<String, String>[] thisData = (HashMap<String, String>[]) res
						.toArray(new HashMap<?, ?>[0]);
				thisTable.loadData(thisData);
				JsonUtil.debug("Completed populating table " + k);
			} else {
				messages.get(crit).setVisible(true);
				JsonUtil.debug("Could not populate null results into table");
			}
		}
	}
}
