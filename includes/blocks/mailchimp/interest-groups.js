export const InterestGroups = ({ listData, visibility }) => {
	if (!listData?.interest_groups?.length) {
		return null;
	}

	return (
		<>
			{listData.interest_groups.map(
				(group) =>
					group &&
					group.id &&
					visibility &&
					visibility[group.id] === 'on' &&
					group.type !== 'hidden' && (
						<div key={group.id}>
							<div className="mc_interests_header">{group.title}</div>
							<div className="mc_interest">
								{group.type === 'checkboxes' &&
									group.groups.map((choice) => (
										<>
											<label
												htmlFor={`mc_interest_${group.id}_${choice.id}`}
												className="mc_interest_label"
											>
												<input
													id={`mc_interest_${group.id}_${choice.id}`}
													type="checkbox"
													name={`group[${group.id}][${choice.id}]`}
													value={choice.id}
													className="mc_interest"
												/>
												{choice.name}
											</label>
											<br />
										</>
									))}
								{group.type === 'radio' &&
									group.groups.map((choice) => (
										<>
											<input
												id={`mc_interest_${group.id}_${choice.id}`}
												type="radio"
												name={`group[${group.id}]`}
												value={choice.id}
												className="mc_interest"
											/>
											<label
												htmlFor={`mc_interest_${group.id}_${choice.id}`}
												className="mc_interest_label"
											>
												{choice.name}
											</label>
											<br />
										</>
									))}
								{group.type === 'dropdown' && (
									<select name={`group[${group.id}]`}>
										{group.groups.map((choice) => (
											<option key={choice.id} value={choice.id}>
												{choice.name}
											</option>
										))}
									</select>
								)}
							</div>
						</div>
					),
			)}
		</>
	);
};
